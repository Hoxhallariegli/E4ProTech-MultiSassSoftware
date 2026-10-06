import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:mobile_gateway/core/widgets/premium_widgets.dart';
import 'package:mobile_gateway/l10n/device_token_localization.dart';
import '../cubit/device_token_cubit.dart';
import '../cubit/device_token_state.dart';
import '../../data/device_token_repository.dart';
import 'package:mobile_gateway/services/api_service.dart';
import 'package:mobile_gateway/services/auth_service.dart';

class DeviceTokenFormPage extends StatefulWidget {
  final Map<String, dynamic>? item;
  const DeviceTokenFormPage({super.key, this.item});
  @override State<DeviceTokenFormPage> createState() => _DeviceTokenFormPageState();
}

class _DeviceTokenFormPageState extends State<DeviceTokenFormPage> {
  final _formKey = GlobalKey<FormState>();
  final repository = DeviceTokenRepository();
  bool _loading = true;

  final _deviceNameController = TextEditingController();
  final _fcmTokenController = TextEditingController();
  String? _platform;
  bool _isSmsGateway = false;

  List<Map<String, dynamic>> _barberShopOptions = [];
  int? _barberShopId;
  List<Map<String, dynamic>> _userOptions = [];
  String? _userId;

  @override void initState() { super.initState(); _init(); }

  Future<void> _init() async {
    _deviceNameController.text = widget.item?['device_name']?.toString() ?? 'Mobile Device';
    _fcmTokenController.text = widget.item?['fcm_token']?.toString() ?? '';
    _platform = widget.item?['platform']?.toString() ?? 'android';
    _isSmsGateway = widget.item?['is_sms_gateway'] == true || widget.item?['is_sms_gateway'] == 1 || widget.item?['is_sms_gateway'] == '1';

    try {
      _barberShopOptions = await repository.lookup('barber-shops');
      if (widget.item?['barber_shop_id'] != null) _barberShopId = int.tryParse(widget.item!['barber_shop_id'].toString());
      if (_barberShopId == null && AuthService.instance.user?['is_admin'] != true) _barberShopId = AuthService.instance.user?['barber_shop_id'] as int?;
      _userOptions = await repository.lookup('users');
      if (widget.item?['user_id'] != null) _userId = widget.item!['user_id'].toString();
    } catch (_) {
    } finally { if (mounted) setState(() => _loading = false); }
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
        const SizedBox(height: 18), Align(alignment: Alignment.centerLeft, child: Text(device_tokenTr(sheetContext, 'field.barber_shop_id'), style: const TextStyle(fontSize: 21, fontWeight: FontWeight.w800))),
        const SizedBox(height: 14), TextField(decoration: InputDecoration(prefixIcon: const Icon(Icons.search_rounded), hintText: device_tokenTr(sheetContext, 'form.search'), border: const OutlineInputBorder()), onChanged: (q) => setSheet(() => filtered = _barberShopOptions.where((e) => _displayName(e).toLowerCase().contains(q.toLowerCase())).toList())),
        const SizedBox(height: 12), Expanded(child: ListView.separated(itemCount: filtered.length, separatorBuilder: (_, __) => const Divider(height: 1), itemBuilder: (_, i) { final option = filtered[i]; return ListTile(title: Text(_displayName(option), style: const TextStyle(fontWeight: FontWeight.w700)), trailing: option['id'].toString() == _barberShopId?.toString() ? const Icon(Icons.check_circle_rounded) : null, onTap: () => Navigator.pop(sheetContext, option)); })),
      ])))),
    );
    if (selected != null) setState(() => _barberShopId = int.tryParse(selected['id'].toString()));
  }

  Future<void> _pickuserId() async {
    if (AuthService.instance.user?['is_admin'] != true) return;
    var filtered = List<Map<String, dynamic>>.from(_userOptions);
    final selected = await showModalBottomSheet<Map<String, dynamic>>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Theme.of(context).colorScheme.surface,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(30))),
      builder: (sheetContext) => StatefulBuilder(builder: (context, setSheet) => SizedBox(height: MediaQuery.of(context).size.height * .72, child: Padding(padding: const EdgeInsets.all(20), child: Column(children: [
        Container(width: 42, height: 4, decoration: BoxDecoration(color: Theme.of(context).dividerColor, borderRadius: BorderRadius.circular(10))),
        const SizedBox(height: 18), Align(alignment: Alignment.centerLeft, child: Text(device_tokenTr(sheetContext, 'field.user_id'), style: const TextStyle(fontSize: 21, fontWeight: FontWeight.w800))),
        const SizedBox(height: 14), TextField(decoration: InputDecoration(prefixIcon: const Icon(Icons.search_rounded), hintText: device_tokenTr(sheetContext, 'form.search'), border: const OutlineInputBorder()), onChanged: (q) => setSheet(() => filtered = _userOptions.where((e) => _displayName(e).toLowerCase().contains(q.toLowerCase())).toList())),
        const SizedBox(height: 12), Expanded(child: ListView.separated(itemCount: filtered.length, separatorBuilder: (_, __) => const Divider(height: 1), itemBuilder: (_, i) { final option = filtered[i]; return ListTile(title: Text(_displayName(option), style: const TextStyle(fontWeight: FontWeight.w700)), trailing: option['id'].toString() == _userId?.toString() ? const Icon(Icons.check_circle_rounded) : null, onTap: () => Navigator.pop(sheetContext, option)); })),
      ])))),
    );
    if (selected != null) setState(() => _userId = selected['id'].toString());
  }

  String _displayName(Map<String, dynamic> item) {
    return (item['name'] ?? item['title'] ?? 'ID: ' + item['id'].toString()).toString();
  }

  Future<void> _save(BuildContext context) async {
    if (!_formKey.currentState!.validate()) return;
    final payload = <String, dynamic>{};
    payload['barber_shop_id'] = _barberShopId;
    payload['user_id'] = _userId;
    payload['device_name'] = _deviceNameController.text.trim();
    payload['fcm_token'] = _fcmTokenController.text.trim();
    payload['platform'] = _platform;
    payload['is_sms_gateway'] = _isSmsGateway;

    context.read<DeviceTokenCubit>().save(payload, id: widget.item?['id']);
  }

  @override Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    const gatewayColor = Color(0xFF059669);

    return BlocProvider(
      create: (_) => DeviceTokenCubit(repository),
      child: BlocListener<DeviceTokenCubit, DeviceTokenState>(
        listener: (context, state) {
          if (state is DeviceTokenSaved) Navigator.pop(context, true);
          if (state is DeviceTokenFailure) {
            final message = state.message.trim().isEmpty ? device_tokenTr(context, 'form.save_error') : state.message;
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
            title: Text(widget.item == null ? device_tokenTr(context, 'form.create_title') : device_tokenTr(context, 'form.edit_title'), style: const TextStyle(fontWeight: FontWeight.w800)),
          ),
          body: _loading ? const Center(child: CircularProgressIndicator.adaptive()) : Form(key: _formKey, child: Builder(builder: (formContext) => ListView(padding: const EdgeInsets.fromLTRB(20, 12, 20, 120), children: [
            _FormHeader(isEdit: widget.item != null),
            const SizedBox(height: 22),
            (AuthService.instance.user?['is_admin'] == true)
              ? _FieldShell(label: device_tokenTr(context, 'field.barber_shop_id'), child: InkWell(onTap: _pickbarberShopId, borderRadius: BorderRadius.circular(16), child: Container(padding: const EdgeInsets.all(16), decoration: BoxDecoration(border: Border.all(color: Theme.of(context).colorScheme.outlineVariant), borderRadius: BorderRadius.circular(16)), child: Row(children: [Expanded(child: Text(_displayName(_barberShopOptions.firstWhere((e) => e['id'].toString() == _barberShopId?.toString(), orElse: () => {'id': '', 'name': device_tokenTr(context, 'form.select')})))), const Icon(Icons.keyboard_arrow_down_rounded)]))))
              : _FieldShell(label: device_tokenTr(context, 'field.barber_shop_id'), child: Container(padding: const EdgeInsets.all(16), width: double.infinity, decoration: BoxDecoration(color: theme.colorScheme.surfaceContainerHighest.withOpacity(0.5), border: Border.all(color: theme.colorScheme.outlineVariant), borderRadius: BorderRadius.circular(16)), child: Text(AuthService.instance.user?['business']?['name'] ?? '', style: const TextStyle(fontWeight: FontWeight.bold)))),
            _FieldShell(label: device_tokenTr(context, 'field.user_id'), child: InkWell(onTap: _pickuserId, borderRadius: BorderRadius.circular(16), child: Container(padding: const EdgeInsets.all(16), decoration: BoxDecoration(border: Border.all(color: Theme.of(context).colorScheme.outlineVariant), borderRadius: BorderRadius.circular(16)), child: Row(children: [Expanded(child: Text(_displayName(_userOptions.firstWhere((e) => e['id'].toString() == _userId?.toString(), orElse: () => {'id': '', 'name': device_tokenTr(context, 'form.select')})))), const Icon(Icons.keyboard_arrow_down_rounded)])))),
            _FieldShell(label: 'Emri i Pajisjes (Device Name)', child: TextFormField(controller: _deviceNameController, decoration: const InputDecoration(hintText: 'p.sh. Mobile Device (Mario) / Telefoni Banak', border: InputBorder.none, isDense: true), validator: (v) { if (v == null || v.trim().isEmpty) return device_tokenTr(context, 'form.required'); return null; })),
            _FieldShell(label: device_tokenTr(context, 'field.fcm_token'), child: TextFormField(controller: _fcmTokenController, decoration: InputDecoration(hintText: device_tokenTr(context, 'field.fcm_token'), border: InputBorder.none, isDense: true), validator: (v) { if (v == null || v.trim().isEmpty) return device_tokenTr(context, 'form.required'); return null; })),
            _FieldShell(label: device_tokenTr(context, 'field.platform'), child: DropdownButtonFormField<String>(value: ['android', 'ios'].contains(_platform) ? _platform : null, items: ['android', 'ios'].map((v) => DropdownMenuItem<String>(value: v, child: Text(v))).toList(), onChanged: (v) => setState(() => _platform = v), validator: (v) { if (v == null || v.isEmpty) return device_tokenTr(context, 'form.select'); return null; }, decoration: const InputDecoration(border: InputBorder.none, isDense: true))),

            Container(
              margin: const EdgeInsets.only(bottom: 14),
              decoration: BoxDecoration(
                color: isDark ? const Color(0xFF1E212B) : theme.colorScheme.surfaceContainerHighest.withOpacity(.5),
                border: Border.all(color: isDark ? const Color(0xFF3B4052) : theme.colorScheme.outlineVariant, width: 1.2),
                borderRadius: BorderRadius.circular(18),
              ),
              child: SwitchListTile.adaptive(
                contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 2),
                title: const Row(
                  children: [
                    Icon(Icons.smartphone_rounded, size: 18, color: gatewayColor),
                    SizedBox(width: 8),
                    Text('Cakto si SMS Gateway', style: TextStyle(fontSize: 13.5, fontWeight: FontWeight.bold)),
                  ],
                ),
                subtitle: Text(_isSmsGateway ? 'Pajisja është SMS Gateway aktiv për dërgimin e mesazheve' : 'Pajisje standarde për njoftime push', style: TextStyle(fontSize: 11, color: _isSmsGateway ? gatewayColor : Colors.grey)),
                value: _isSmsGateway,
                onChanged: (v) => setState(() => _isSmsGateway = v),
              ),
            ),

            const SizedBox(height: 14),
            BlocBuilder<DeviceTokenCubit, DeviceTokenState>(builder: (context, state) => PremiumButton(onPressed: () => _save(context), label: state is DeviceTokenSaving ? device_tokenTr(context, 'form.saving') : device_tokenTr(context, 'form.save'), icon: Icons.check_rounded, loading: state is DeviceTokenSaving, expand: true)),
          ]))),
        ),
      ),
    );
  }

  @override void dispose() {
    _deviceNameController.dispose();
    _fcmTokenController.dispose();
    super.dispose();
  }
}

class _FormHeader extends StatelessWidget {
  final bool isEdit;
  const _FormHeader({required this.isEdit});
  @override Widget build(BuildContext context) => Container(padding: const EdgeInsets.all(20), decoration: BoxDecoration(gradient: LinearGradient(colors: [Theme.of(context).colorScheme.primaryContainer, Theme.of(context).colorScheme.secondaryContainer]), borderRadius: BorderRadius.circular(24)), child: Row(children: [Container(width: 48, height: 48, decoration: BoxDecoration(color: Theme.of(context).colorScheme.surface.withOpacity(.7), borderRadius: BorderRadius.circular(15)), child: Icon(isEdit ? Icons.edit_rounded : Icons.add_rounded)), const SizedBox(width: 14), Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(isEdit ? device_tokenTr(context, 'form.update_record') : device_tokenTr(context, 'form.new_record'), style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900)), const SizedBox(height: 3), Text(isEdit ? device_tokenTr(context, 'form.review_update') : device_tokenTr(context, 'form.fill_create'), style: TextStyle(fontSize: 12, color: Theme.of(context).colorScheme.onSurfaceVariant))]))]));
}

class _FieldShell extends StatelessWidget {
  final String label; final Widget child;
  const _FieldShell({required this.label, required this.child});
  @override Widget build(BuildContext context) => Container(margin: const EdgeInsets.only(bottom: 14), padding: const EdgeInsets.fromLTRB(16, 12, 16, 6), decoration: BoxDecoration(color: Theme.of(context).colorScheme.surfaceContainerHighest.withOpacity(.35), border: Border.all(color: Theme.of(context).colorScheme.outlineVariant.withOpacity(.7)), borderRadius: BorderRadius.circular(18)), child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(label, style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: Theme.of(context).colorScheme.onSurfaceVariant)), child]));
}
