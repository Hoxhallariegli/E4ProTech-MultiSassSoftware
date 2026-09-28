import 'dart:convert';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:image_picker/image_picker.dart';
import 'package:mobile_gateway/core/widgets/premium_widgets.dart';
import 'package:mobile_gateway/core/widgets/premium_image_picker.dart';
import 'package:mobile_gateway/l10n/barber_localization.dart';
import '../cubit/barber_cubit.dart';
import '../cubit/barber_state.dart';
import '../../data/barber_repository.dart';
import 'package:mobile_gateway/services/api_service.dart';
import 'package:mobile_gateway/services/auth_service.dart';

class BarberFormPage extends StatefulWidget {
  final Map<String, dynamic>? item;
  const BarberFormPage({super.key, this.item});
  @override State<BarberFormPage> createState() => _BarberFormPageState();
}

class _BarberFormPageState extends State<BarberFormPage> {
  final _formKey = GlobalKey<FormState>();
  final repository = BarberRepository();
  bool _loading = true;
  String? _photoPath;

  final _nameController = TextEditingController();
  final _phoneController = TextEditingController();
  final _bioController = TextEditingController();
  bool _active = false;

  List<Map<String, dynamic>> _barberShopOptions = [];
  int? _barberShopId;
  List<Map<String, dynamic>> _userOptions = [];
  String? _userId;


  @override void initState() { super.initState(); _init(); }

  Future<void> _init() async {
    _nameController.text = widget.item?['name']?.toString() ?? '';
    _phoneController.text = widget.item?['phone']?.toString() ?? '';
    _bioController.text = widget.item?['bio']?.toString() ?? '';
    _active = widget.item?['active'] == true || widget.item?['active'] == 1 || widget.item?['active'] == '1';

    if (widget.item?['barber_shop_id'] != null) {
      _barberShopId = int.tryParse(widget.item!['barber_shop_id'].toString());
    }
    if (_barberShopId == null && AuthService.instance.user?['is_admin'] != true) {
      _barberShopId = AuthService.instance.user?['barber_shop_id'] as int?;
    }

    try {
      if (AuthService.instance.user?['is_admin'] == true) {
        _barberShopOptions = await repository.lookup('barber-shops');
      }
      _userOptions = await repository.lookup('users');
      if (widget.item?['user_id'] != null) _userId = widget.item!['user_id'].toString();
    } catch (_) {
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _pickbarberShopId() async {
    if ("barber_shop_id" == "barber_shop_id" && AuthService.instance.user?['is_admin'] != true) return;
    var filtered = List<Map<String, dynamic>>.from(_barberShopOptions);
    final selected = await showModalBottomSheet<Map<String, dynamic>>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Theme.of(context).colorScheme.surface,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(30))),
      builder: (sheetContext) => StatefulBuilder(builder: (context, setSheet) => SizedBox(height: MediaQuery.of(context).size.height * .72, child: Padding(padding: const EdgeInsets.all(20), child: Column(children: [
        Container(width: 42, height: 4, decoration: BoxDecoration(color: Theme.of(context).dividerColor, borderRadius: BorderRadius.circular(10))),
        const SizedBox(height: 18), Align(alignment: Alignment.centerLeft, child: Text(barberTr(sheetContext, 'field.barber_shop_id'), style: const TextStyle(fontSize: 21, fontWeight: FontWeight.w800))),
        const SizedBox(height: 14), TextField(decoration: InputDecoration(prefixIcon: const Icon(Icons.search_rounded), hintText: barberTr(sheetContext, 'form.search'), border: const OutlineInputBorder()), onChanged: (q) => setSheet(() => filtered = _barberShopOptions.where((e) => _displayName(e).toLowerCase().contains(q.toLowerCase())).toList())),
        const SizedBox(height: 12), Expanded(child: ListView.separated(itemCount: filtered.length, separatorBuilder: (_, __) => const Divider(height: 1), itemBuilder: (_, i) { final option = filtered[i]; return ListTile(title: Text(_displayName(option), style: const TextStyle(fontWeight: FontWeight.w700)), trailing: option['id'].toString() == _barberShopId?.toString() ? const Icon(Icons.check_circle_rounded) : null, onTap: () => Navigator.pop(sheetContext, option)); })),
      ])))),
    );
    if (selected != null) setState(() => _barberShopId = int.tryParse(selected['id'].toString()));
  }  Future<void> _pickuserId() async {
    if ("user_id" == "barber_shop_id" && AuthService.instance.user?['is_admin'] != true) return;
    var filtered = List<Map<String, dynamic>>.from(_userOptions);
    final selected = await showModalBottomSheet<Map<String, dynamic>>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Theme.of(context).colorScheme.surface,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(30))),
      builder: (sheetContext) => StatefulBuilder(builder: (context, setSheet) => SizedBox(height: MediaQuery.of(context).size.height * .72, child: Padding(padding: const EdgeInsets.all(20), child: Column(children: [
        Container(width: 42, height: 4, decoration: BoxDecoration(color: Theme.of(context).dividerColor, borderRadius: BorderRadius.circular(10))),
        const SizedBox(height: 18), Align(alignment: Alignment.centerLeft, child: Text(barberTr(sheetContext, 'field.user_id'), style: const TextStyle(fontSize: 21, fontWeight: FontWeight.w800))),
        const SizedBox(height: 14), TextField(decoration: InputDecoration(prefixIcon: const Icon(Icons.search_rounded), hintText: barberTr(sheetContext, 'form.search'), border: const OutlineInputBorder()), onChanged: (q) => setSheet(() => filtered = _userOptions.where((e) => _displayName(e).toLowerCase().contains(q.toLowerCase())).toList())),
        const SizedBox(height: 12), Expanded(child: ListView.separated(itemCount: filtered.length, separatorBuilder: (_, __) => const Divider(height: 1), itemBuilder: (_, i) { final option = filtered[i]; return ListTile(title: Text(_displayName(option), style: const TextStyle(fontWeight: FontWeight.w700)), trailing: option['id'].toString() == _userId?.toString() ? const Icon(Icons.check_circle_rounded) : null, onTap: () => Navigator.pop(sheetContext, option)); })),
      ])))),
    );
    if (selected != null) setState(() => _userId = selected['id'].toString());
  }
  Future<void> _pickDate(TextEditingController controller) async {
    final initial = _parseDisplayDate(controller.text) ?? DateTime.now();
    final picked = await showDatePicker(context: context, initialDate: initial, firstDate: DateTime(1900), lastDate: DateTime(2200));
    if (picked != null && mounted) setState(() => controller.text = _formatDisplayDate(picked, includeTime: false));
  }

  Future<void> _pickDateTime(TextEditingController controller) async {
    final initial = _parseDisplayDate(controller.text) ?? DateTime.now();
    final date = await showDatePicker(context: context, initialDate: initial, firstDate: DateTime(1900), lastDate: DateTime(2200));
    if (date == null || !mounted) return;
    final time = await showTimePicker(context: context, initialTime: TimeOfDay.fromDateTime(initial));
    if (time == null || !mounted) return;
    final value = DateTime(date.year, date.month, date.day, time.hour, time.minute);
    setState(() => controller.text = _formatDisplayDate(value, includeTime: true));
  }

  DateTime? _parseDisplayDate(String value) {
    final text = value.trim();
    if (text.isEmpty) return null;
    final display = RegExp(r'^(\d{2})/(\d{2})/(\d{4})(?: (\d{2}):(\d{2}))?$').firstMatch(text);
    if (display != null) {
      return DateTime(
        int.parse(display.group(3)!),
        int.parse(display.group(2)!),
        int.parse(display.group(1)!),
        int.tryParse(display.group(4) ?? '0') ?? 0,
        int.tryParse(display.group(5) ?? '0') ?? 0,
      );
    }
    return DateTime.tryParse(text)?.toLocal();
  }

  String _formatDisplayDate(DateTime value, {required bool includeTime}) {
    final date = '${value.day.toString().padLeft(2, '0')}/${value.month.toString().padLeft(2, '0')}/${value.year}';
    if (!includeTime) return date;
    return '$date ${value.hour.toString().padLeft(2, '0')}:${value.minute.toString().padLeft(2, '0')}';
  }

  String _displayDateTime(dynamic value, {required bool includeTime}) {
    if (value == null || value.toString().trim().isEmpty) return '';
    final parsed = DateTime.tryParse(value.toString())?.toLocal();
    return parsed == null ? value.toString() : _formatDisplayDate(parsed, includeTime: includeTime);
  }

  String? _apiDateValue(String value) {
    final parsed = _parseDisplayDate(value);
    if (parsed == null) return null;
    return '${parsed.year.toString().padLeft(4, '0')}-${parsed.month.toString().padLeft(2, '0')}-${parsed.day.toString().padLeft(2, '0')}';
  }

  String? _apiDateTimeValue(String value) {
    final parsed = _parseDisplayDate(value);
    return parsed?.toIso8601String();
  }

  String _displayName(Map<String, dynamic> item) {
    return (item['name'] ?? item['title'] ?? 'ID: ' + item['id'].toString()).toString();
  }

  Future<void> _save(BuildContext context) async {
    if (!_formKey.currentState!.validate()) return;
    final payload = <String, dynamic>{};
    payload['barber_shop_id'] = _barberShopId;
    payload['user_id'] = _userId;
    payload['name'] = _nameController.text;
    payload['phone'] = _phoneController.text;
    payload['bio'] = _bioController.text;
    payload['active'] = _active;

    final files = <String, String>{};
    if (_photoPath != null) files['photo'] = _photoPath!;

    context.read<BarberCubit>().save(payload, id: widget.item?['id'], files: files);
  }

  @override Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return BlocProvider(
      create: (_) => BarberCubit(repository),
      child: BlocListener<BarberCubit, BarberState>(
        listener: (context, state) {
          if (state is BarberSaved) Navigator.pop(context, true);
          if (state is BarberFailure) {
            final message = state.message.trim().isEmpty ? barberTr(context, 'form.save_error') : state.message;
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
            title: Text(widget.item == null ? barberTr(context, 'form.create_title') : barberTr(context, 'form.edit_title'), style: const TextStyle(fontWeight: FontWeight.w800)),
          ),
          body: _loading ? const Center(child: CircularProgressIndicator.adaptive()) : Form(key: _formKey, child: Builder(builder: (formContext) => ListView(padding: const EdgeInsets.fromLTRB(20, 12, 20, 120), children: [
            const SizedBox(height: 12),
            (AuthService.instance.user?['is_admin'] == true)
              ? _FieldShell(label: barberTr(context, 'field.barber_shop_id'), child: InkWell(onTap: _pickbarberShopId, borderRadius: BorderRadius.circular(16), child: Container(padding: const EdgeInsets.all(16), decoration: BoxDecoration(border: Border.all(color: Theme.of(context).colorScheme.outlineVariant), borderRadius: BorderRadius.circular(16)), child: Row(children: [Expanded(child: Text(_displayName(_barberShopOptions.firstWhere((e) => e['id'].toString() == _barberShopId?.toString(), orElse: () => {'id': '', 'name': barberTr(context, 'form.select')})))), const Icon(Icons.keyboard_arrow_down_rounded)]))))
              : _FieldShell(label: barberTr(context, 'field.barber_shop_id'), child: Container(padding: const EdgeInsets.all(16), width: double.infinity, decoration: BoxDecoration(color: theme.colorScheme.surfaceContainerHighest.withOpacity(0.5), border: Border.all(color: theme.colorScheme.outlineVariant), borderRadius: BorderRadius.circular(16)), child: Text(AuthService.instance.user?['business']?['name'] ?? '', style: const TextStyle(fontWeight: FontWeight.bold)))),
            _FieldShell(label: barberTr(context, 'field.user_id'), child: InkWell(onTap: _pickuserId, borderRadius: BorderRadius.circular(16), child: Container(padding: const EdgeInsets.all(16), decoration: BoxDecoration(border: Border.all(color: Theme.of(context).colorScheme.outlineVariant), borderRadius: BorderRadius.circular(16)), child: Row(children: [Expanded(child: Text(_displayName(_userOptions.firstWhere((e) => e['id'].toString() == _userId?.toString(), orElse: () => {'id': '', 'name': barberTr(context, 'form.select')})))), const Icon(Icons.keyboard_arrow_down_rounded)])))),
            _FieldShell(label: barberTr(context, 'field.name'), child: TextFormField(controller: _nameController,  decoration: InputDecoration(hintText: barberTr(context, 'field.name'), border: InputBorder.none, isDense: true), validator: (v) { if (v == null || v.trim().isEmpty) return barberTr(context, 'form.required');  return null; })),
            _FieldShell(label: barberTr(context, 'field.phone'), child: TextFormField(controller: _phoneController,  decoration: InputDecoration(hintText: barberTr(context, 'field.phone'), border: InputBorder.none, isDense: true), validator: (v) { if (v == null || v.trim().isEmpty) return barberTr(context, 'form.required');  return null; })),
            PremiumImagePicker(label: barberTr(context, 'field.photo'), path: _photoPath, currentUrl: widget.item?['photo'] != null ? '${ApiService.serverUrl}/${widget.item!['photo']}' : null, onPicked: (p) => setState(() => _photoPath = p)),
            _FieldShell(label: barberTr(context, 'field.bio'), child: TextFormField(controller: _bioController,  decoration: InputDecoration(hintText: barberTr(context, 'field.bio'), border: InputBorder.none, isDense: true), validator: (v) {  return null; })),
            Container(decoration: BoxDecoration(border: Border.all(color: Theme.of(context).colorScheme.outlineVariant), borderRadius: BorderRadius.circular(16)), child: SwitchListTile.adaptive(contentPadding: const EdgeInsets.symmetric(horizontal: 16), title: Text(barberTr(context, 'field.active'), style: const TextStyle(fontWeight: FontWeight.w700)), value: _active, onChanged: (v) => setState(() => _active = v))),

            const SizedBox(height: 14),
            BlocBuilder<BarberCubit, BarberState>(builder: (context, state) => PremiumButton(onPressed: () => _save(context), label: state is BarberSaving ? barberTr(context, 'form.saving') : barberTr(context, 'form.save'), icon: Icons.check_rounded, loading: state is BarberSaving, expand: true)),
          ]))),
        ),
      ),
    );
  }

  @override void dispose() {
    _nameController.dispose();
    _phoneController.dispose();
    _bioController.dispose();
    super.dispose();
  }
}
class _FormHeader extends StatelessWidget {
  final bool isEdit;
  const _FormHeader({required this.isEdit});
  @override Widget build(BuildContext context) => Container(padding: const EdgeInsets.all(20), decoration: BoxDecoration(gradient: LinearGradient(colors: [Theme.of(context).colorScheme.primaryContainer, Theme.of(context).colorScheme.secondaryContainer]), borderRadius: BorderRadius.circular(24)), child: Row(children: [Container(width: 48, height: 48, decoration: BoxDecoration(color: Theme.of(context).colorScheme.surface.withOpacity(.7), borderRadius: BorderRadius.circular(15)), child: Icon(isEdit ? Icons.edit_rounded : Icons.add_rounded)), const SizedBox(width: 14), Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(isEdit ? barberTr(context, 'form.update_record') : barberTr(context, 'form.new_record'), style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900)), const SizedBox(height: 3), Text(isEdit ? barberTr(context, 'form.review_update') : barberTr(context, 'form.fill_create'), style: TextStyle(fontSize: 12, color: Theme.of(context).colorScheme.onSurfaceVariant))]))]));
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
