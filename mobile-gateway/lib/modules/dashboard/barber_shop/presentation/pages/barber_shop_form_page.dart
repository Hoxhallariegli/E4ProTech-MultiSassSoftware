import 'dart:convert';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:image_picker/image_picker.dart';
import 'package:mobile_gateway/core/widgets/premium_widgets.dart';
import 'package:mobile_gateway/core/widgets/premium_image_picker.dart';
import 'package:mobile_gateway/l10n/barber_shop_localization.dart';
import '../cubit/barber_shop_cubit.dart';
import '../cubit/barber_shop_state.dart';
import '../../data/barber_shop_repository.dart';
import 'package:mobile_gateway/services/api_service.dart';
import 'package:mobile_gateway/services/auth_service.dart';

class BarberShopFormPage extends StatefulWidget {
  final Map<String, dynamic>? item;
  const BarberShopFormPage({super.key, this.item});
  @override State<BarberShopFormPage> createState() => _BarberShopFormPageState();
}

class _BarberShopFormPageState extends State<BarberShopFormPage> {
  final _formKey = GlobalKey<FormState>();
  final repository = BarberShopRepository();
  bool _loading = true;
  String? _logoPath;
  String? _bannerPath;

  final _nameController = TextEditingController();
  final _appNameController = TextEditingController();
  final _slugController = TextEditingController();
  final _primaryColorController = TextEditingController();
  final _secondaryColorController = TextEditingController();
  final _trialEndsAtController = TextEditingController();
  final _expiresAtController = TextEditingController();
  bool _active = false;
  bool _smsEnabled = false;
  final _timezoneController = TextEditingController();
  final _maxNoShowBeforeBlockController = TextEditingController();

  List<Map<String, dynamic>> _ownerOptions = [];
  String? _ownerId;


  @override void initState() { super.initState(); _init(); }

  Future<void> _init() async {
    _nameController.text = widget.item?['name']?.toString() ?? '';
    _appNameController.text = widget.item?['app_name']?.toString() ?? '';
    _slugController.text = widget.item?['slug']?.toString() ?? '';
    _primaryColorController.text = widget.item?['primary_color']?.toString() ?? '';
    _secondaryColorController.text = widget.item?['secondary_color']?.toString() ?? '';
    _trialEndsAtController.text = widget.item?['trial_ends_at']?.toString() ?? '';
    _expiresAtController.text = widget.item?['expires_at']?.toString() ?? '';
    _active = widget.item?['active'] == true || widget.item?['active'] == 1 || widget.item?['active'] == '1';
    _smsEnabled = widget.item?['sms_enabled'] == true || widget.item?['sms_enabled'] == 1 || widget.item?['sms_enabled'] == '1';
    _timezoneController.text = widget.item?['timezone']?.toString() ?? '';
    _maxNoShowBeforeBlockController.text = widget.item?['max_no_show_before_block']?.toString() ?? '';

    try {
    _ownerOptions = await repository.lookup('users');
    if (widget.item?['owner_id'] != null) _ownerId = widget.item!['owner_id'].toString();
    } catch (_) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(barber_shopTr(context, 'form.could_not_load')), behavior: SnackBarBehavior.floating));
    } finally { if (mounted) setState(() => _loading = false); }
  }

  Future<void> _pickownerId() async {
    if ("owner_id" == "barber_shop_id" && AuthService.instance.user?['is_admin'] != true) return;
    var filtered = List<Map<String, dynamic>>.from(_ownerOptions);
    final selected = await showModalBottomSheet<Map<String, dynamic>>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Theme.of(context).colorScheme.surface,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(30))),
      builder: (sheetContext) => StatefulBuilder(builder: (context, setSheet) => SizedBox(height: MediaQuery.of(context).size.height * .72, child: Padding(padding: const EdgeInsets.all(20), child: Column(children: [
        Container(width: 42, height: 4, decoration: BoxDecoration(color: Theme.of(context).dividerColor, borderRadius: BorderRadius.circular(10))),
        const SizedBox(height: 18), Align(alignment: Alignment.centerLeft, child: Text(barber_shopTr(sheetContext, 'field.owner_id'), style: const TextStyle(fontSize: 21, fontWeight: FontWeight.w800))),
        const SizedBox(height: 14), TextField(decoration: InputDecoration(prefixIcon: const Icon(Icons.search_rounded), hintText: barber_shopTr(sheetContext, 'form.search'), border: const OutlineInputBorder()), onChanged: (q) => setSheet(() => filtered = _ownerOptions.where((e) => _displayName(e).toLowerCase().contains(q.toLowerCase())).toList())),
        const SizedBox(height: 12), Expanded(child: ListView.separated(itemCount: filtered.length, separatorBuilder: (_, __) => const Divider(height: 1), itemBuilder: (_, i) { final option = filtered[i]; return ListTile(title: Text(_displayName(option), style: const TextStyle(fontWeight: FontWeight.w700)), trailing: option['id'].toString() == _ownerId?.toString() ? const Icon(Icons.check_circle_rounded) : null, onTap: () => Navigator.pop(sheetContext, option)); })),
      ])))),
    );
    if (selected != null) setState(() => _ownerId = selected['id'].toString());
  }
  Future<void> _pickDate(TextEditingController controller) async {
    final initial = DateTime.tryParse(controller.text) ?? DateTime.now();
    final picked = await showDatePicker(context: context, initialDate: initial, firstDate: DateTime(1900), lastDate: DateTime(2200));
    if (picked != null && mounted) setState(() => controller.text = picked.toIso8601String().split('T').first);
  }

  Future<void> _pickDateTime(TextEditingController controller) async {
    final initial = DateTime.tryParse(controller.text) ?? DateTime.now();
    final date = await showDatePicker(context: context, initialDate: initial, firstDate: DateTime(1900), lastDate: DateTime(2200));
    if (date == null || !mounted) return;
    final time = await showTimePicker(context: context, initialTime: TimeOfDay.fromDateTime(initial));
    if (time == null || !mounted) return;
    final value = DateTime(date.year, date.month, date.day, time.hour, time.minute);
    setState(() => controller.text = value.toIso8601String());
  }

  String _displayName(Map<String, dynamic> item) {
    return (item['name'] ?? item['title'] ?? 'ID: ' + item['id'].toString()).toString();
  }

  Future<void> _save(BuildContext context) async {
    if (!_formKey.currentState!.validate()) return;
    final payload = <String, dynamic>{};
    payload['owner_id'] = _ownerId;
    payload['name'] = _nameController.text;
    payload['app_name'] = _appNameController.text;
    payload['slug'] = _slugController.text;
    payload['primary_color'] = _primaryColorController.text;
    payload['secondary_color'] = _secondaryColorController.text;
    payload['trial_ends_at'] = _trialEndsAtController.text.isEmpty ? null : _trialEndsAtController.text;
    payload['expires_at'] = _expiresAtController.text.isEmpty ? null : _expiresAtController.text;
    payload['active'] = _active;
    payload['sms_enabled'] = _smsEnabled;
    payload['timezone'] = _timezoneController.text;
    payload['max_no_show_before_block'] = int.tryParse(_maxNoShowBeforeBlockController.text);

    final files = <String, String>{};
    if (_logoPath != null) files['logo'] = _logoPath!;
    if (_bannerPath != null) files['banner'] = _bannerPath!;

    context.read<BarberShopCubit>().save(payload, id: widget.item?['id'], files: files);
  }

  @override Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return BlocProvider(
      create: (_) => BarberShopCubit(repository),
      child: BlocListener<BarberShopCubit, BarberShopState>(
        listener: (context, state) {
          if (state is BarberShopSaved) Navigator.pop(context, true);
          if (state is BarberShopFailure) {
            final message = state.message.trim().isEmpty ? barber_shopTr(context, 'form.save_error') : state.message;
            ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(message), behavior: SnackBarBehavior.floating, duration: const Duration(seconds: 4)));
          }
        },
        child: Scaffold(
          appBar: AppBar(title: Text(widget.item == null ? barber_shopTr(context, 'form.create_title') : barber_shopTr(context, 'form.edit_title'), style: const TextStyle(fontWeight: FontWeight.w800))),
          body: _loading ? const Center(child: CircularProgressIndicator.adaptive()) : Form(key: _formKey, child: Builder(builder: (formContext) => ListView(padding: const EdgeInsets.fromLTRB(20, 12, 20, 120), children: [
            _FormHeader(isEdit: widget.item != null),
            const SizedBox(height: 22),
            _FieldShell(label: barber_shopTr(context, 'field.owner_id'), child: InkWell(onTap: _pickownerId, borderRadius: BorderRadius.circular(16), child: Container(padding: const EdgeInsets.all(16), decoration: BoxDecoration(border: Border.all(color: Theme.of(context).colorScheme.outlineVariant), borderRadius: BorderRadius.circular(16)), child: Row(children: [Expanded(child: Text(_displayName(_ownerOptions.firstWhere((e) => e['id'].toString() == _ownerId?.toString(), orElse: () => {'id': '', 'name': barber_shopTr(context, 'form.select')})))), const Icon(Icons.keyboard_arrow_down_rounded)])))),
            _FieldShell(label: barber_shopTr(context, 'field.name'), child: TextFormField(controller: _nameController,  decoration: InputDecoration(hintText: barber_shopTr(context, 'field.name'), border: InputBorder.none, isDense: true), validator: (v) { if (v == null || v.trim().isEmpty) return barber_shopTr(context, 'form.required');  return null; })),
            _FieldShell(label: barber_shopTr(context, 'field.app_name'), child: TextFormField(controller: _appNameController,  decoration: InputDecoration(hintText: barber_shopTr(context, 'field.app_name'), border: InputBorder.none, isDense: true), validator: (v) { if (v == null || v.trim().isEmpty) return barber_shopTr(context, 'form.required');  return null; })),
            _FieldShell(label: barber_shopTr(context, 'field.slug'), child: TextFormField(controller: _slugController,  decoration: InputDecoration(hintText: barber_shopTr(context, 'field.slug'), border: InputBorder.none, isDense: true), validator: (v) { if (v == null || v.trim().isEmpty) return barber_shopTr(context, 'form.required');  return null; })),
            PremiumImagePicker(label: barber_shopTr(context, 'field.logo'), path: _logoPath, currentUrl: widget.item?['logo'] != null ? '${ApiService.serverUrl}/${widget.item!['logo']}' : null, onPicked: (p) => setState(() => _logoPath = p)),
            PremiumImagePicker(label: barber_shopTr(context, 'field.banner'), path: _bannerPath, currentUrl: widget.item?['banner'] != null ? '${ApiService.serverUrl}/${widget.item!['banner']}' : null, onPicked: (p) => setState(() => _bannerPath = p)),
            _FieldShell(label: barber_shopTr(context, 'field.primary_color'), child: TextFormField(controller: _primaryColorController,  decoration: InputDecoration(hintText: barber_shopTr(context, 'field.primary_color'), border: InputBorder.none, isDense: true), validator: (v) { if (v == null || v.trim().isEmpty) return barber_shopTr(context, 'form.required');  return null; })),
            _FieldShell(label: barber_shopTr(context, 'field.secondary_color'), child: TextFormField(controller: _secondaryColorController,  decoration: InputDecoration(hintText: barber_shopTr(context, 'field.secondary_color'), border: InputBorder.none, isDense: true), validator: (v) { if (v == null || v.trim().isEmpty) return barber_shopTr(context, 'form.required');  return null; })),
            _FieldShell(label: barber_shopTr(context, 'field.trial_ends_at'), child: InkWell(onTap: () => _pickDateTime(_trialEndsAtController), borderRadius: BorderRadius.circular(12), child: InputDecorator(decoration: InputDecoration(border: InputBorder.none, isDense: true, suffixIcon: const Icon(Icons.event_rounded)), child: Text(_trialEndsAtController.text.isEmpty ? barber_shopTr(context, 'form.select_datetime') : _trialEndsAtController.text, style: TextStyle(color: _trialEndsAtController.text.isEmpty ? Theme.of(context).colorScheme.onSurfaceVariant : null, fontWeight: FontWeight.w600))))),
            _FieldShell(label: barber_shopTr(context, 'field.expires_at'), child: InkWell(onTap: () => _pickDateTime(_expiresAtController), borderRadius: BorderRadius.circular(12), child: InputDecorator(decoration: InputDecoration(border: InputBorder.none, isDense: true, suffixIcon: const Icon(Icons.event_rounded)), child: Text(_expiresAtController.text.isEmpty ? barber_shopTr(context, 'form.select_datetime') : _expiresAtController.text, style: TextStyle(color: _expiresAtController.text.isEmpty ? Theme.of(context).colorScheme.onSurfaceVariant : null, fontWeight: FontWeight.w600))))),
            Container(decoration: BoxDecoration(border: Border.all(color: Theme.of(context).colorScheme.outlineVariant), borderRadius: BorderRadius.circular(16)), child: SwitchListTile.adaptive(contentPadding: const EdgeInsets.symmetric(horizontal: 16), title: Text(barber_shopTr(context, 'field.active'), style: const TextStyle(fontWeight: FontWeight.w700)), value: _active, onChanged: (v) => setState(() => _active = v))),
            Container(decoration: BoxDecoration(border: Border.all(color: Theme.of(context).colorScheme.outlineVariant), borderRadius: BorderRadius.circular(16)), child: SwitchListTile.adaptive(contentPadding: const EdgeInsets.symmetric(horizontal: 16), title: Text(barber_shopTr(context, 'field.sms_enabled'), style: const TextStyle(fontWeight: FontWeight.w700)), value: _smsEnabled, onChanged: (v) => setState(() => _smsEnabled = v))),
            _FieldShell(label: barber_shopTr(context, 'field.timezone'), child: TextFormField(controller: _timezoneController,  decoration: InputDecoration(hintText: barber_shopTr(context, 'field.timezone'), border: InputBorder.none, isDense: true), validator: (v) { if (v == null || v.trim().isEmpty) return barber_shopTr(context, 'form.required');  return null; })),
            _FieldShell(label: barber_shopTr(context, 'field.max_no_show_before_block'), child: TextFormField(controller: _maxNoShowBeforeBlockController, keyboardType: const TextInputType.numberWithOptions(decimal: false), inputFormatters: [FilteringTextInputFormatter.digitsOnly],  decoration: InputDecoration(hintText: barber_shopTr(context, 'field.max_no_show_before_block'), border: InputBorder.none, isDense: true), validator: (v) { if (v != null && v.isNotEmpty && int.tryParse(v) == null) return barber_shopTr(context, 'form.invalid_integer');  return null; })),

            const SizedBox(height: 14),
            BlocBuilder<BarberShopCubit, BarberShopState>(builder: (context, state) => PremiumButton(onPressed: () => _save(context), label: state is BarberShopSaving ? barber_shopTr(context, 'form.saving') : barber_shopTr(context, 'form.save'), icon: Icons.check_rounded, loading: state is BarberShopSaving, expand: true)),
          ]))),
        ),
      ),
    );
  }

  @override void dispose() {
    _nameController.dispose();
    _appNameController.dispose();
    _slugController.dispose();
    _primaryColorController.dispose();
    _secondaryColorController.dispose();
    _trialEndsAtController.dispose();
    _expiresAtController.dispose();
    _timezoneController.dispose();
    _maxNoShowBeforeBlockController.dispose();
    super.dispose();
  }
}
class _FormHeader extends StatelessWidget {
  final bool isEdit;
  const _FormHeader({required this.isEdit});
  @override Widget build(BuildContext context) => Container(padding: const EdgeInsets.all(20), decoration: BoxDecoration(gradient: LinearGradient(colors: [Theme.of(context).colorScheme.primaryContainer, Theme.of(context).colorScheme.secondaryContainer]), borderRadius: BorderRadius.circular(24)), child: Row(children: [Container(width: 48, height: 48, decoration: BoxDecoration(color: Theme.of(context).colorScheme.surface.withOpacity(.7), borderRadius: BorderRadius.circular(15)), child: Icon(isEdit ? Icons.edit_rounded : Icons.add_rounded)), const SizedBox(width: 14), Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(isEdit ? barber_shopTr(context, 'form.update_record') : barber_shopTr(context, 'form.new_record'), style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900)), const SizedBox(height: 3), Text(isEdit ? barber_shopTr(context, 'form.review_update') : barber_shopTr(context, 'form.fill_create'), style: TextStyle(fontSize: 12, color: Theme.of(context).colorScheme.onSurfaceVariant))]))]));
}

class _FieldShell extends StatelessWidget {
  final String label; final Widget child;
  const _FieldShell({required this.label, required this.child});
  @override Widget build(BuildContext context) => Container(margin: const EdgeInsets.only(bottom: 14), padding: const EdgeInsets.fromLTRB(16, 12, 16, 6), decoration: BoxDecoration(color: Theme.of(context).colorScheme.surfaceContainerHighest.withOpacity(.35), border: Border.all(color: Theme.of(context).colorScheme.outlineVariant.withOpacity(.7)), borderRadius: BorderRadius.circular(18)), child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(label, style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: Theme.of(context).colorScheme.onSurfaceVariant)), child]));
}

class _FilePickerCard extends StatelessWidget {
  final String? current, path; final VoidCallback onPick;
  const _FilePickerCard({this.current, this.path, required this.onPick});
  @override Widget build(BuildContext context) => InkWell(onTap: onPick, borderRadius: BorderRadius.circular(20), child: Container(height: 150, margin: const EdgeInsets.only(bottom: 14), decoration: BoxDecoration(borderRadius: BorderRadius.circular(20), border: Border.all(color: Theme.of(context).colorScheme.outlineVariant), color: Theme.of(context).colorScheme.surfaceContainerHighest.withOpacity(.25)), child: path != null ? ClipRRect(borderRadius: BorderRadius.circular(20), child: Image.file(File(path!), fit: BoxFit.cover, width: double.infinity)) : current != null ? ClipRRect(borderRadius: BorderRadius.circular(20), child: Image.network('${ApiService.serverUrl}/$current', fit: BoxFit.cover, width: double.infinity)) : const Column(mainAxisAlignment: MainAxisAlignment.center, children: [Icon(Icons.cloud_upload_outlined, size: 34), SizedBox(height: 8), Text('Tap to choose image', style: TextStyle(fontWeight: FontWeight.w700)), SizedBox(height: 3), Text('PNG, JPG', style: TextStyle(fontSize: 11, color: Colors.grey))])));
}