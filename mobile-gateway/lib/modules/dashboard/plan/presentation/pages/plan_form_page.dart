import 'dart:convert';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:image_picker/image_picker.dart';
import 'package:mobile_gateway/core/widgets/premium_widgets.dart';
import 'package:mobile_gateway/core/widgets/premium_image_picker.dart';
import 'package:mobile_gateway/l10n/plan_localization.dart';
import '../cubit/plan_cubit.dart';
import '../cubit/plan_state.dart';
import '../../data/plan_repository.dart';
import 'package:mobile_gateway/services/api_service.dart';
import 'package:mobile_gateway/services/auth_service.dart';

class PlanFormPage extends StatefulWidget {
  final Map<String, dynamic>? item;
  const PlanFormPage({super.key, this.item});
  @override State<PlanFormPage> createState() => _PlanFormPageState();
}

class _PlanFormPageState extends State<PlanFormPage> {
  final _formKey = GlobalKey<FormState>();
  final repository = PlanRepository();
  bool _loading = true;

  final _nameController = TextEditingController();
  final _priceController = TextEditingController();
  final _durationMonthsController = TextEditingController();
  final _maxBarbersController = TextEditingController();
  final _maxServicesController = TextEditingController();
  final _maxShopsController = TextEditingController();
  bool _active = false;



  @override void initState() { super.initState(); _init(); }

  Future<void> _init() async {
    _nameController.text = widget.item?['name']?.toString() ?? '';
    final _initialprice = widget.item?['price'];
    _priceController.text = _initialprice == null ? '' : (double.tryParse(_initialprice.toString())?.toStringAsFixed(2) ?? _initialprice.toString());
    _durationMonthsController.text = widget.item?['duration_months']?.toString() ?? '';
    _maxBarbersController.text = widget.item?['max_barbers']?.toString() ?? '';
    _maxServicesController.text = widget.item?['max_services']?.toString() ?? '';
    _maxShopsController.text = widget.item?['max_shops']?.toString() ?? '';
    _active = widget.item?['active'] == true || widget.item?['active'] == 1 || widget.item?['active'] == '1';

    try {
    } catch (_) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(planTr(context, 'form.could_not_load')), behavior: SnackBarBehavior.floating));
    } finally { if (mounted) setState(() => _loading = false); }
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
    payload['name'] = _nameController.text;
    payload['price'] = double.tryParse(_priceController.text);
    payload['duration_months'] = int.tryParse(_durationMonthsController.text);
    payload['max_barbers'] = int.tryParse(_maxBarbersController.text);
    payload['max_services'] = int.tryParse(_maxServicesController.text);
    payload['max_shops'] = int.tryParse(_maxShopsController.text);
    payload['active'] = _active;

    final files = <String, String>{};

    context.read<PlanCubit>().save(payload, id: widget.item?['id']);
  }

  @override Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return BlocProvider(
      create: (_) => PlanCubit(repository),
      child: BlocListener<PlanCubit, PlanState>(
        listener: (context, state) {
          if (state is PlanSaved) Navigator.pop(context, true);
          if (state is PlanFailure) {
            final message = state.message.trim().isEmpty ? planTr(context, 'form.save_error') : state.message;
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
            title: Text(widget.item == null ? planTr(context, 'form.create_title') : planTr(context, 'form.edit_title'), style: const TextStyle(fontWeight: FontWeight.w800)),
          ),
          body: _loading ? const Center(child: CircularProgressIndicator.adaptive()) : Form(key: _formKey, child: Builder(builder: (formContext) => ListView(padding: const EdgeInsets.fromLTRB(20, 12, 20, 120), children: [
            _FormHeader(isEdit: widget.item != null),
            const SizedBox(height: 22),
            _FieldShell(label: planTr(context, 'field.name'), child: TextFormField(controller: _nameController,  decoration: InputDecoration(hintText: planTr(context, 'field.name'), border: InputBorder.none, isDense: true), validator: (v) { if (v == null || v.trim().isEmpty) return planTr(context, 'form.required');  return null; })),
            _FieldShell(label: planTr(context, 'field.price'), child: TextFormField(controller: _priceController, keyboardType: const TextInputType.numberWithOptions(decimal: true), inputFormatters: [TextInputFormatter.withFunction((oldValue, newValue) { final text = newValue.text; if (text.isEmpty || RegExp(r'^\d*\.?\d{0,2}$').hasMatch(text)) return newValue; return oldValue; })],  decoration: InputDecoration(hintText: planTr(context, 'field.price'), border: InputBorder.none, isDense: true), validator: (v) { if (v == null || v.trim().isEmpty) return planTr(context, 'form.required'); if (v != null && v.isNotEmpty && (double.tryParse(v) == null || !RegExp(r'^\d+(\.\d{1,2})?$').hasMatch(v))) return 'Enter a valid number with up to 2 decimals';  return null; })),
            _FieldShell(label: planTr(context, 'field.duration_months'), child: TextFormField(controller: _durationMonthsController, keyboardType: const TextInputType.numberWithOptions(decimal: false), inputFormatters: [FilteringTextInputFormatter.digitsOnly],  decoration: InputDecoration(hintText: planTr(context, 'field.duration_months'), border: InputBorder.none, isDense: true), validator: (v) { if (v == null || v.trim().isEmpty) return planTr(context, 'form.required'); if (v != null && v.isNotEmpty && int.tryParse(v) == null) return planTr(context, 'form.invalid_integer');  return null; })),
            _FieldShell(label: planTr(context, 'field.max_barbers'), child: TextFormField(controller: _maxBarbersController, keyboardType: const TextInputType.numberWithOptions(decimal: false), inputFormatters: [FilteringTextInputFormatter.digitsOnly],  decoration: InputDecoration(hintText: planTr(context, 'field.max_barbers'), border: InputBorder.none, isDense: true), validator: (v) { if (v == null || v.trim().isEmpty) return planTr(context, 'form.required'); if (v != null && v.isNotEmpty && int.tryParse(v) == null) return planTr(context, 'form.invalid_integer');  return null; })),
            _FieldShell(label: planTr(context, 'field.max_services'), child: TextFormField(controller: _maxServicesController, keyboardType: const TextInputType.numberWithOptions(decimal: false), inputFormatters: [FilteringTextInputFormatter.digitsOnly],  decoration: InputDecoration(hintText: planTr(context, 'field.max_services'), border: InputBorder.none, isDense: true), validator: (v) { if (v == null || v.trim().isEmpty) return planTr(context, 'form.required'); if (v != null && v.isNotEmpty && int.tryParse(v) == null) return planTr(context, 'form.invalid_integer');  return null; })),
            _FieldShell(label: planTr(context, 'field.max_shops'), child: TextFormField(controller: _maxShopsController, keyboardType: const TextInputType.numberWithOptions(decimal: false), inputFormatters: [FilteringTextInputFormatter.digitsOnly],  decoration: InputDecoration(hintText: planTr(context, 'field.max_shops'), border: InputBorder.none, isDense: true), validator: (v) { if (v == null || v.trim().isEmpty) return planTr(context, 'form.required'); if (v != null && v.isNotEmpty && int.tryParse(v) == null) return planTr(context, 'form.invalid_integer');  return null; })),
            Container(decoration: BoxDecoration(border: Border.all(color: Theme.of(context).colorScheme.outlineVariant), borderRadius: BorderRadius.circular(16)), child: SwitchListTile.adaptive(contentPadding: const EdgeInsets.symmetric(horizontal: 16), title: Text(planTr(context, 'field.active'), style: const TextStyle(fontWeight: FontWeight.w700)), value: _active, onChanged: (v) => setState(() => _active = v))),

            const SizedBox(height: 14),
            BlocBuilder<PlanCubit, PlanState>(builder: (context, state) => PremiumButton(onPressed: () => _save(context), label: state is PlanSaving ? planTr(context, 'form.saving') : planTr(context, 'form.save'), icon: Icons.check_rounded, loading: state is PlanSaving, expand: true)),
          ]))),
        ),
      ),
    );
  }

  @override void dispose() {
    _nameController.dispose();
    _priceController.dispose();
    _durationMonthsController.dispose();
    _maxBarbersController.dispose();
    _maxServicesController.dispose();
    _maxShopsController.dispose();
    super.dispose();
  }
}
class _FormHeader extends StatelessWidget {
  final bool isEdit;
  const _FormHeader({required this.isEdit});
  @override Widget build(BuildContext context) => Container(padding: const EdgeInsets.all(20), decoration: BoxDecoration(gradient: LinearGradient(colors: [Theme.of(context).colorScheme.primaryContainer, Theme.of(context).colorScheme.secondaryContainer]), borderRadius: BorderRadius.circular(24)), child: Row(children: [Container(width: 48, height: 48, decoration: BoxDecoration(color: Theme.of(context).colorScheme.surface.withOpacity(.7), borderRadius: BorderRadius.circular(15)), child: Icon(isEdit ? Icons.edit_rounded : Icons.add_rounded)), const SizedBox(width: 14), Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(isEdit ? planTr(context, 'form.update_record') : planTr(context, 'form.new_record'), style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900)), const SizedBox(height: 3), Text(isEdit ? planTr(context, 'form.review_update') : planTr(context, 'form.fill_create'), style: TextStyle(fontSize: 12, color: Theme.of(context).colorScheme.onSurfaceVariant))]))]));
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