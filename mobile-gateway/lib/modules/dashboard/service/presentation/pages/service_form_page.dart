import 'dart:convert';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:image_picker/image_picker.dart';
import 'package:mobile_gateway/core/widgets/premium_widgets.dart';
import 'package:mobile_gateway/core/widgets/premium_image_picker.dart';
import 'package:mobile_gateway/l10n/service_localization.dart';
import '../cubit/service_cubit.dart';
import '../cubit/service_state.dart';
import '../../data/service_repository.dart';
import 'package:mobile_gateway/services/api_service.dart';
import 'package:mobile_gateway/services/auth_service.dart';

class ServiceFormPage extends StatefulWidget {
  final Map<String, dynamic>? item;
  const ServiceFormPage({super.key, this.item});
  @override State<ServiceFormPage> createState() => _ServiceFormPageState();
}

class _ServiceFormPageState extends State<ServiceFormPage> {
  final _formKey = GlobalKey<FormState>();
  final repository = ServiceRepository();
  bool _loading = true;

  final _nameController = TextEditingController();
  final _descriptionController = TextEditingController();
  final _priceController = TextEditingController();
  final _durationMinutesController = TextEditingController();
  final _categoryController = TextEditingController();
  bool _active = true;

  List<Map<String, dynamic>> _barberShopOptions = [];
  int? _barberShopId;

  @override void initState() { super.initState(); _init(); }

  Future<void> _init() async {
    _nameController.text = widget.item?['name']?.toString() ?? '';
    _descriptionController.text = widget.item?['description']?.toString() ?? '';
    final _initialprice = widget.item?['price'];
    _priceController.text = _initialprice == null ? '' : (double.tryParse(_initialprice.toString())?.toStringAsFixed(2) ?? _initialprice.toString());
    _durationMinutesController.text = widget.item?['duration_minutes']?.toString() ?? '';
    _categoryController.text = widget.item?['category']?.toString() ?? '';
    _active = widget.item?['active'] == true || widget.item?['active'] == 1 || widget.item?['active'] == '1' || widget.item?['active'] == null;

    if (widget.item?['barber_shop_id'] != null) {
      _barberShopId = int.tryParse(widget.item!['barber_shop_id'].toString());
    }
    if (_barberShopId == null) {
      final user = AuthService.instance.user;
      final raw = user?['barber_shop_id'] ?? user?['business_id'] ?? user?['business']?['id'] ?? user?['shop_id'];
      if (raw != null) {
        _barberShopId = int.tryParse(raw.toString());
      }
    }

    try {
      if (AuthService.instance.user?['is_admin'] == true) {
        _barberShopOptions = await repository.lookup('barber-shops');
      }
    } catch (_) {}

    if (mounted) setState(() => _loading = false);
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
        const SizedBox(height: 18), Align(alignment: Alignment.centerLeft, child: Text(serviceTr(sheetContext, 'field.barber_shop_id'), style: const TextStyle(fontSize: 21, fontWeight: FontWeight.w800))),
        const SizedBox(height: 14), TextField(decoration: InputDecoration(prefixIcon: const Icon(Icons.search_rounded), hintText: serviceTr(sheetContext, 'form.search'), border: const OutlineInputBorder()), onChanged: (q) => setSheet(() => filtered = _barberShopOptions.where((e) => _displayName(e).toLowerCase().contains(q.toLowerCase())).toList())),
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

    var shopId = _barberShopId;
    if (shopId == null) {
      final user = AuthService.instance.user;
      final raw = user?['barber_shop_id'] ?? user?['business_id'] ?? user?['business']?['id'] ?? user?['shop_id'];
      if (raw != null) shopId = int.tryParse(raw.toString());
    }

    payload['barber_shop_id'] = shopId;
    payload['name'] = _nameController.text.trim();
    payload['description'] = _descriptionController.text.trim();
    payload['price'] = double.tryParse(_priceController.text.trim());
    payload['duration_minutes'] = int.tryParse(_durationMinutesController.text.trim());
    payload['category'] = _categoryController.text.trim();
    payload['active'] = _active;

    context.read<ServiceCubit>().save(payload, id: widget.item?['id']);
  }

  @override Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return BlocProvider(
      create: (_) => ServiceCubit(repository),
      child: BlocListener<ServiceCubit, ServiceState>(
        listener: (context, state) {
          if (state is ServiceSaved) Navigator.pop(context, true);
          if (state is ServiceFailure) {
            final message = state.message.trim().isEmpty ? serviceTr(context, 'form.save_error') : state.message;
            ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(message), behavior: SnackBarBehavior.floating, duration: const Duration(seconds: 4)));
          }
        },
        child: Scaffold(
          appBar: AppBar(
            leading: IconButton(
              tooltip: 'Back',
              icon: const Icon(Icons.arrow_back_rounded),
              onPressed: () {
                if (Navigator.of(context).canPop()) {
                  Navigator.of(context).pop();
                } else {
                  Navigator.of(context).pushNamedAndRemoveUntil('/dashboard', (route) => false);
                }
              },
            ),
            title: Text(widget.item == null ? serviceTr(context, 'form.create_title') : serviceTr(context, 'form.edit_title'), style: const TextStyle(fontWeight: FontWeight.w800)),
          ),
          body: _loading ? const Center(child: CircularProgressIndicator.adaptive()) : Form(key: _formKey, child: Builder(builder: (formContext) => ListView(padding: const EdgeInsets.fromLTRB(20, 12, 20, 120), children: [
            const SizedBox(height: 12),
            (AuthService.instance.user?['is_admin'] == true)
              ? _FieldShell(label: serviceTr(context, 'field.barber_shop_id'), child: InkWell(onTap: _pickbarberShopId, borderRadius: BorderRadius.circular(16), child: Container(padding: const EdgeInsets.all(16), decoration: BoxDecoration(border: Border.all(color: Theme.of(context).colorScheme.outlineVariant), borderRadius: BorderRadius.circular(16)), child: Row(children: [Expanded(child: Text(_displayName(_barberShopOptions.firstWhere((e) => e['id'].toString() == _barberShopId?.toString(), orElse: () => {'id': '', 'name': serviceTr(context, 'form.select')})))), const Icon(Icons.keyboard_arrow_down_rounded)]))))
              : _FieldShell(label: serviceTr(context, 'field.barber_shop_id'), child: Container(padding: const EdgeInsets.all(16), width: double.infinity, decoration: BoxDecoration(color: theme.colorScheme.surfaceContainerHighest.withOpacity(0.5), border: Border.all(color: theme.colorScheme.outlineVariant), borderRadius: BorderRadius.circular(16)), child: Text(AuthService.instance.user?['business']?['name'] ?? 'BERBERANA 2', style: const TextStyle(fontWeight: FontWeight.bold)))),
            _FieldShell(label: serviceTr(context, 'field.name'), child: TextFormField(controller: _nameController,  decoration: InputDecoration(hintText: serviceTr(context, 'field.name'), border: InputBorder.none, isDense: true), validator: (v) { if (v == null || v.trim().isEmpty) return serviceTr(context, 'form.required');  return null; })),
            _FieldShell(label: serviceTr(context, 'field.description'), child: TextFormField(controller: _descriptionController,  decoration: InputDecoration(hintText: serviceTr(context, 'field.description'), border: InputBorder.none, isDense: true), validator: (v) {  return null; })),
            _FieldShell(label: serviceTr(context, 'field.price'), child: TextFormField(controller: _priceController, keyboardType: const TextInputType.numberWithOptions(decimal: true), inputFormatters: [TextInputFormatter.withFunction((oldValue, newValue) { final text = newValue.text; if (text.isEmpty || RegExp(r'^\d*\.?\d{0,2}$').hasMatch(text)) return newValue; return oldValue; })],  decoration: InputDecoration(hintText: serviceTr(context, 'field.price'), border: InputBorder.none, isDense: true), validator: (v) { if (v == null || v.trim().isEmpty) return serviceTr(context, 'form.required'); if (v != null && v.isNotEmpty && (double.tryParse(v) == null || !RegExp(r'^\d+(\.\d{1,2})?$').hasMatch(v))) return 'Enter a valid number with up to 2 decimals';  return null; })),
            _FieldShell(label: serviceTr(context, 'field.duration_minutes'), child: TextFormField(controller: _durationMinutesController, keyboardType: const TextInputType.numberWithOptions(decimal: false), inputFormatters: [FilteringTextInputFormatter.digitsOnly],  decoration: InputDecoration(hintText: serviceTr(context, 'field.duration_minutes'), border: InputBorder.none, isDense: true), validator: (v) { if (v == null || v.trim().isEmpty) return serviceTr(context, 'form.required'); if (v != null && v.isNotEmpty && int.tryParse(v) == null) return serviceTr(context, 'form.invalid_integer');  return null; })),
            _FieldShell(label: serviceTr(context, 'field.category'), child: TextFormField(controller: _categoryController,  decoration: InputDecoration(hintText: serviceTr(context, 'field.category'), border: InputBorder.none, isDense: true), validator: (v) {  return null; })),
            Container(decoration: BoxDecoration(border: Border.all(color: Theme.of(context).colorScheme.outlineVariant), borderRadius: BorderRadius.circular(16)), child: SwitchListTile.adaptive(contentPadding: const EdgeInsets.symmetric(horizontal: 16), title: Text(serviceTr(context, 'field.active'), style: const TextStyle(fontWeight: FontWeight.w700)), value: _active, onChanged: (v) => setState(() => _active = v))),

            const SizedBox(height: 14),
            BlocBuilder<ServiceCubit, ServiceState>(builder: (context, state) => PremiumButton(onPressed: () => _save(context), label: state is ServiceSaving ? serviceTr(context, 'form.saving') : serviceTr(context, 'form.save'), icon: Icons.check_rounded, loading: state is ServiceSaving, expand: true)),
          ]))),
        ),
      ),
    );
  }

  @override void dispose() {
    _nameController.dispose();
    _descriptionController.dispose();
    _priceController.dispose();
    _durationMinutesController.dispose();
    _categoryController.dispose();
    super.dispose();
  }
}
class _FormHeader extends StatelessWidget {
  final bool isEdit;
  const _FormHeader({required this.isEdit});
  @override Widget build(BuildContext context) => Container(padding: const EdgeInsets.all(20), decoration: BoxDecoration(gradient: LinearGradient(colors: [Theme.of(context).colorScheme.primaryContainer, Theme.of(context).colorScheme.secondaryContainer]), borderRadius: BorderRadius.circular(24)), child: Row(children: [Container(width: 48, height: 48, decoration: BoxDecoration(color: Theme.of(context).colorScheme.surface.withOpacity(.7), borderRadius: BorderRadius.circular(15)), child: Icon(isEdit ? Icons.edit_rounded : Icons.add_rounded)), const SizedBox(width: 14), Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(isEdit ? serviceTr(context, 'form.update_record') : serviceTr(context, 'form.new_record'), style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900)), const SizedBox(height: 3), Text(isEdit ? serviceTr(context, 'form.review_update') : serviceTr(context, 'form.fill_create'), style: TextStyle(fontSize: 12, color: Theme.of(context).colorScheme.onSurfaceVariant))]))]));
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
