import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:mobile_gateway/core/widgets/premium_widgets.dart';
import 'package:mobile_gateway/l10n/message_template_localization.dart';
import '../cubit/message_template_cubit.dart';
import '../cubit/message_template_state.dart';
import '../../data/message_template_repository.dart';
import 'package:mobile_gateway/services/api_service.dart';
import 'package:mobile_gateway/services/auth_service.dart';

class MessageTemplateFormPage extends StatefulWidget {
  final Map<String, dynamic>? item;
  const MessageTemplateFormPage({super.key, this.item});
  @override State<MessageTemplateFormPage> createState() => _MessageTemplateFormPageState();
}

class _MessageTemplateFormPageState extends State<MessageTemplateFormPage> {
  final _formKey = GlobalKey<FormState>();
  final repository = MessageTemplateRepository();
  bool _loading = true;

  String? _channel;
  String? _type;
  final _contentSqController = TextEditingController();
  final _contentEnController = TextEditingController();

  List<Map<String, dynamic>> _barberShopOptions = [];
  int? _barberShopId;

  @override void initState() { super.initState(); _init(); }

  Future<void> _init() async {
    _channel = widget.item?['channel']?.toString();
    _type = widget.item?['type']?.toString();

    final itemContent = widget.item?['content'];
    if (itemContent is Map) {
      _contentSqController.text = itemContent['sq']?.toString() ?? '';
      _contentEnController.text = itemContent['en']?.toString() ?? '';
    } else if (itemContent is String && itemContent.startsWith('{')) {
      try {
        final decoded = jsonDecode(itemContent);
        if (decoded is Map) {
          _contentSqController.text = decoded['sq']?.toString() ?? '';
          _contentEnController.text = decoded['en']?.toString() ?? '';
        } else {
          _contentSqController.text = itemContent;
        }
      } catch (_) {
        _contentSqController.text = itemContent;
      }
    } else {
      _contentSqController.text = widget.item?['content_sq']?.toString() ?? widget.item?['content']?.toString() ?? '';
      _contentEnController.text = widget.item?['content_en']?.toString() ?? '';
    }

    if (widget.item?['barber_shop_id'] != null) {
      _barberShopId = int.tryParse(widget.item!['barber_shop_id'].toString());
    }
    _barberShopId ??= AuthService.instance.user?['barber_shop_id'] as int?;

    try {
      if (AuthService.instance.user?['is_admin'] == true) {
        _barberShopOptions = await repository.lookup('barber-shops');
      }
    } catch (_) {}

    if (mounted) {
      setState(() => _loading = false);
    }
  }

  Future<void> _pickbarberShopId() async {
    if (AuthService.instance.user?['is_admin'] != true) return;
    var filtered = List<Map<String, dynamic>>.from(_barberShopOptions);
    final selected = await showModalBottomSheet<Map<String, dynamic>>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Theme.of(context).colorScheme.surface,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(30))),
      builder: (sheetContext) => StatefulBuilder(builder: (context, setSheet) => SizedBox(height: MediaQuery.of(context).size.height * .72, child: Padding(padding: const EdgeInsets.all(20), child: Column(children: [
        Container(width: 42, height: 4, decoration: BoxDecoration(color: Theme.of(context).dividerColor, borderRadius: BorderRadius.circular(10))),
        const SizedBox(height: 18), Align(alignment: Alignment.centerLeft, child: Text(message_templateTr(sheetContext, 'field.barber_shop_id'), style: const TextStyle(fontSize: 21, fontWeight: FontWeight.w800))),
        const SizedBox(height: 14), TextField(decoration: InputDecoration(prefixIcon: const Icon(Icons.search_rounded), hintText: message_templateTr(sheetContext, 'form.search'), border: const OutlineInputBorder()), onChanged: (q) => setSheet(() => filtered = _barberShopOptions.where((e) => _displayName(e).toLowerCase().contains(q.toLowerCase())).toList())),
        const SizedBox(height: 12), Expanded(child: ListView.separated(itemCount: filtered.length, separatorBuilder: (_, __) => const Divider(height: 1), itemBuilder: (_, i) { final option = filtered[i]; return ListTile(title: Text(_displayName(option), style: const TextStyle(fontWeight: FontWeight.w700)), trailing: option['id'].toString() == _barberShopId?.toString() ? const Icon(Icons.check_circle_rounded) : null, onTap: () => Navigator.pop(sheetContext, option)); })),
      ])))),
    );
    if (selected != null) setState(() => _barberShopId = int.tryParse(selected['id'].toString()));
  }

  String _displayName(Map<String, dynamic> item) {
    return (item['name'] ?? item['title'] ?? 'ID: ' + item['id'].toString()).toString();
  }

  Future<void> _save(BuildContext context) async {
    if (!_formKey.currentState!.validate()) return;
    final payload = <String, dynamic>{};
    payload['barber_shop_id'] = _barberShopId;
    payload['channel'] = _channel;
    payload['type'] = _type;

    final sqText = _contentSqController.text.trim();
    final enText = _contentEnController.text.trim();

    // Send individual language content in 'content' (max 160) so server validation 'max:160' passes 100%!
    payload['content'] = sqText.isNotEmpty ? sqText : enText;
    payload['content_sq'] = sqText;
    payload['content_en'] = enText;

    context.read<MessageTemplateCubit>().save(payload, id: widget.item?['id']);
  }

  @override Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return BlocProvider(
      create: (_) => MessageTemplateCubit(repository),
      child: BlocListener<MessageTemplateCubit, MessageTemplateState>(
        listener: (context, state) {
          if (state is MessageTemplateSaved) Navigator.pop(context, true);
          if (state is MessageTemplateFailure) {
            final message = state.message.trim().isEmpty ? message_templateTr(context, 'form.save_error') : state.message;
            ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(message), behavior: SnackBarBehavior.floating, duration: const Duration(seconds: 4)));
          }
        },
        child: Scaffold(
          appBar: AppBar(
            leading: IconButton(
              tooltip: 'Back',
              icon: const Icon(Icons.arrow_back_rounded),
              onPressed: () => Navigator.of(context).maybePop(),
            ),
            title: Text(widget.item == null ? message_templateTr(context, 'form.create_title') : message_templateTr(context, 'form.edit_title'), style: const TextStyle(fontWeight: FontWeight.w800)),
          ),
          body: _loading ? const Center(child: CircularProgressIndicator.adaptive()) : Form(key: _formKey, child: Builder(builder: (formContext) => ListView(padding: const EdgeInsets.fromLTRB(20, 12, 20, 120), children: [
            _FormHeader(isEdit: widget.item != null),
            const SizedBox(height: 22),
            (AuthService.instance.user?['is_admin'] == true)
              ? _FieldShell(label: message_templateTr(context, 'field.barber_shop_id'), child: InkWell(onTap: _pickbarberShopId, borderRadius: BorderRadius.circular(16), child: Container(padding: const EdgeInsets.all(16), decoration: BoxDecoration(border: Border.all(color: Theme.of(context).colorScheme.outlineVariant), borderRadius: BorderRadius.circular(16)), child: Row(children: [Expanded(child: Text(_displayName(_barberShopOptions.firstWhere((e) => e['id'].toString() == _barberShopId?.toString(), orElse: () => {'id': '', 'name': message_templateTr(context, 'form.select')})))), const Icon(Icons.keyboard_arrow_down_rounded)]))))
              : _FieldShell(label: message_templateTr(context, 'field.barber_shop_id'), child: Container(padding: const EdgeInsets.all(16), width: double.infinity, decoration: BoxDecoration(color: theme.colorScheme.surfaceContainerHighest.withOpacity(0.5), border: Border.all(color: theme.colorScheme.outlineVariant), borderRadius: BorderRadius.circular(16)), child: Text(AuthService.instance.user?['business']?['name'] ?? '', style: const TextStyle(fontWeight: FontWeight.bold)))),
            _FieldShell(label: message_templateTr(context, 'field.channel'), child: DropdownButtonFormField<String>(value: ['sms', 'whatsapp'].contains(_channel) ? _channel : null, items: ['sms', 'whatsapp'].map((v) => DropdownMenuItem<String>(value: v, child: Text(v))).toList(), onChanged: (v) => setState(() => _channel = v), validator: (v) { if (v == null || v.isEmpty) return message_templateTr(context, 'form.select'); return null; }, decoration: const InputDecoration(border: InputBorder.none, isDense: true))),
            _FieldShell(label: message_templateTr(context, 'field.type'), child: DropdownButtonFormField<String>(value: ['reminder', 'confirmation', 'welcome'].contains(_type) ? _type : null, items: ['reminder', 'confirmation', 'welcome'].map((v) => DropdownMenuItem<String>(value: v, child: Text(v))).toList(), onChanged: (v) => setState(() => _type = v), validator: (v) { if (v == null || v.isEmpty) return message_templateTr(context, 'form.select'); return null; }, decoration: const InputDecoration(border: InputBorder.none, isDense: true))),

            // Multi-language Input 1: Shqip (SQ)
            _FieldShell(
              label: '🇦🇱 Përmbajtja në Shqip (SQ)',
              child: TextFormField(
                controller: _contentSqController,
                maxLength: 160,
                maxLines: 3,
                decoration: const InputDecoration(
                  hintText: 'Përmbajtja e mesazhit në Shqip...',
                  border: InputBorder.none,
                  isDense: true,
                ),
                validator: (v) {
                  if (v == null || v.trim().isEmpty) return message_templateTr(context, 'form.required');
                  if (v.trim().length > 160) return 'Maksimumi i lejuar për SMS është 160 karaktere.';
                  return null;
                },
              ),
            ),

            // Multi-language Input 2: English (EN)
            _FieldShell(
              label: '🇬🇧 Content in English (EN)',
              child: TextFormField(
                controller: _contentEnController,
                maxLength: 160,
                maxLines: 3,
                decoration: const InputDecoration(
                  hintText: 'Message template content in English...',
                  border: InputBorder.none,
                  isDense: true,
                ),
                validator: (v) {
                  if (v != null && v.trim().length > 160) return 'Max allowed for SMS is 160 characters.';
                  return null;
                },
              ),
            ),

            const SizedBox(height: 14),
            BlocBuilder<MessageTemplateCubit, MessageTemplateState>(builder: (context, state) => PremiumButton(onPressed: () => _save(context), label: state is MessageTemplateSaving ? message_templateTr(context, 'form.saving') : message_templateTr(context, 'form.save'), icon: Icons.check_rounded, loading: state is MessageTemplateSaving, expand: true)),
          ]))),
        ),
      ),
    );
  }

  @override void dispose() {
    _contentSqController.dispose();
    _contentEnController.dispose();
    super.dispose();
  }
}

class _FormHeader extends StatelessWidget {
  final bool isEdit;
  const _FormHeader({required this.isEdit});
  @override Widget build(BuildContext context) => Container(padding: const EdgeInsets.all(20), decoration: BoxDecoration(gradient: LinearGradient(colors: [Theme.of(context).colorScheme.primaryContainer, Theme.of(context).colorScheme.secondaryContainer]), borderRadius: BorderRadius.circular(24)), child: Row(children: [Container(width: 48, height: 48, decoration: BoxDecoration(color: Theme.of(context).colorScheme.surface.withOpacity(.7), borderRadius: BorderRadius.circular(15)), child: Icon(isEdit ? Icons.edit_rounded : Icons.add_rounded)), const SizedBox(width: 14), Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(isEdit ? message_templateTr(context, 'form.update_record') : message_templateTr(context, 'form.new_record'), style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900)), const SizedBox(height: 3), Text(isEdit ? message_templateTr(context, 'form.review_update') : message_templateTr(context, 'form.fill_create'), style: TextStyle(fontSize: 12, color: Theme.of(context).colorScheme.onSurfaceVariant))]))]));
}

class _FieldShell extends StatelessWidget {
  final String label; final Widget child;
  const _FieldShell({required this.label, required this.child});
  @override Widget build(BuildContext context) => Container(margin: const EdgeInsets.only(bottom: 14), padding: const EdgeInsets.fromLTRB(16, 12, 16, 6), decoration: BoxDecoration(color: Theme.of(context).colorScheme.surfaceContainerHighest.withOpacity(.35), border: Border.all(color: Theme.of(context).colorScheme.outlineVariant.withOpacity(.7)), borderRadius: BorderRadius.circular(18)), child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(label, style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: Theme.of(context).colorScheme.onSurfaceVariant)), child]));
}
