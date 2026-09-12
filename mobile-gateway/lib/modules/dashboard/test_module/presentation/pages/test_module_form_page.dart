import 'dart:convert';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:image_picker/image_picker.dart';
import '../../../../../core/widgets/premium_widgets.dart';
import '../../../../../core/widgets/premium_image_picker.dart';
import '../../../../../l10n/test_module_localization.dart';
import '../cubit/test_module_cubit.dart';
import '../cubit/test_module_state.dart';
import '../../data/test_module_repository.dart';
import '../../../../../services/api_service.dart';

class TestModuleFormPage extends StatefulWidget {
  final Map<String, dynamic>? item;
  const TestModuleFormPage({super.key, this.item});
  @override State<TestModuleFormPage> createState() => _TestModuleFormPageState();
}

class _TestModuleFormPageState extends State<TestModuleFormPage> {
  final _formKey = GlobalKey<FormState>();
  final repository = TestModuleRepository();
  bool _loading = true;
  String? _imagePath;
  String? _coverPhotoPath;

  final _nameController = TextEditingController();
  final _descriptionController = TextEditingController();
  final _qtyController = TextEditingController();
  final _priceController = TextEditingController();
  bool _isActive = false;
  final _dueDateController = TextEditingController();
  final _eventAtController = TextEditingController();
  String? _priority;

  List<Map<String, dynamic>> _userOptions = [];
  String? _userId;


  @override void initState() { super.initState(); _init(); }

  Future<void> _init() async {
    _nameController.text = widget.item?['name']?.toString() ?? '';
    _descriptionController.text = widget.item?['description']?.toString() ?? '';
    final _initialqty = widget.item?['qty'];
    _qtyController.text = _initialqty == null ? '' : (double.tryParse(_initialqty.toString())?.toStringAsFixed(2) ?? _initialqty.toString());
    final _initialprice = widget.item?['price'];
    _priceController.text = _initialprice == null ? '' : (double.tryParse(_initialprice.toString())?.toStringAsFixed(2) ?? _initialprice.toString());
    _isActive = widget.item?['is_active'] == true || widget.item?['is_active'] == 1 || widget.item?['is_active'] == '1';
    _dueDateController.text = widget.item?['due_date']?.toString() ?? '';
    _eventAtController.text = widget.item?['event_at']?.toString() ?? '';
    _priority = widget.item?['priority']?.toString();

    try {
    _userOptions = await repository.lookup('users');
    if (widget.item?['user_id'] != null) _userId = widget.item!['user_id'].toString();
    } catch (_) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(test_moduleTr(context, 'form.could_not_load')), behavior: SnackBarBehavior.floating));
    } finally { if (mounted) setState(() => _loading = false); }
  }

  Future<void> _pickuserId() async {
    var filtered = List<Map<String, dynamic>>.from(_userOptions);
    final selected = await showModalBottomSheet<Map<String, dynamic>>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Theme.of(context).colorScheme.surface,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(30))),
      builder: (sheetContext) => StatefulBuilder(builder: (context, setSheet) => SizedBox(height: MediaQuery.of(context).size.height * .72, child: Padding(padding: const EdgeInsets.all(20), child: Column(children: [
        Container(width: 42, height: 4, decoration: BoxDecoration(color: Theme.of(context).dividerColor, borderRadius: BorderRadius.circular(10))),
        const SizedBox(height: 18), Align(alignment: Alignment.centerLeft, child: Text(test_moduleTr(context, 'field.user_id'), style: const TextStyle(fontSize: 21, fontWeight: FontWeight.w800))),
        const SizedBox(height: 14), TextField(decoration: InputDecoration(prefixIcon: const Icon(Icons.search_rounded), hintText: test_moduleTr(context, 'form.search'), border: const OutlineInputBorder()), onChanged: (q) => setSheet(() => filtered = _userOptions.where((e) => _displayName(e).toLowerCase().contains(q.toLowerCase())).toList())),
        const SizedBox(height: 12), Expanded(child: ListView.separated(itemCount: filtered.length, separatorBuilder: (_, __) => const Divider(height: 1), itemBuilder: (_, i) { final option = filtered[i]; return ListTile(title: Text(_displayName(option), style: const TextStyle(fontWeight: FontWeight.w700)), trailing: option['id'].toString() == _userId?.toString() ? const Icon(Icons.check_circle_rounded) : null, onTap: () => Navigator.pop(sheetContext, option)); })),
      ])))),
    );
    if (selected != null) setState(() => _userId = selected['id'].toString());
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
    payload['name'] = _nameController.text;
    payload['description'] = _descriptionController.text;
    payload['qty'] = double.tryParse(_qtyController.text);
    payload['price'] = double.tryParse(_priceController.text);
    payload['is_active'] = _isActive;
    payload['due_date'] = _dueDateController.text.isEmpty ? null : _dueDateController.text;
    payload['event_at'] = _eventAtController.text.isEmpty ? null : _eventAtController.text;
    payload['user_id'] = _userId;
    payload['priority'] = _priority;

    final files = <String, String>{};
    if (_imagePath != null) files['image'] = _imagePath!;
    if (_coverPhotoPath != null) files['cover_photo'] = _coverPhotoPath!;

    context.read<TestModuleCubit>().save(payload, id: widget.item?['id'], files: files);
  }

  @override Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return BlocProvider(
      create: (_) => TestModuleCubit(repository),
      child: BlocListener<TestModuleCubit, TestModuleState>(
        listener: (context, state) {
          if (state is TestModuleSaved) Navigator.pop(context, true);
          if (state is TestModuleFailure) {
            final message = state.message.trim().isEmpty ? test_moduleTr(context, 'form.save_error') : state.message;
            ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(message), behavior: SnackBarBehavior.floating, duration: const Duration(seconds: 4)));
          }
        },
        child: Scaffold(
          appBar: AppBar(title: Text(widget.item == null ? test_moduleTr(context, 'form.create_title') : test_moduleTr(context, 'form.edit_title'), style: const TextStyle(fontWeight: FontWeight.w800))),
          body: _loading ? const Center(child: CircularProgressIndicator.adaptive()) : Form(key: _formKey, child: Builder(builder: (formContext) => ListView(padding: const EdgeInsets.fromLTRB(20, 12, 20, 120), children: [
            _FormHeader(isEdit: widget.item != null),
            const SizedBox(height: 22),
            _FieldShell(label: test_moduleTr(context, 'field.name'), child: TextFormField(controller: _nameController,  decoration: InputDecoration(hintText: test_moduleTr(context, 'field.name'), border: InputBorder.none, isDense: true), validator: (v) { if (v == null || v.trim().isEmpty) return test_moduleTr(context, 'form.required');  return null; })),
            _FieldShell(label: test_moduleTr(context, 'field.description'), child: TextFormField(controller: _descriptionController,  decoration: InputDecoration(hintText: test_moduleTr(context, 'field.description'), border: InputBorder.none, isDense: true), validator: (v) {  return null; })),
            _FieldShell(label: test_moduleTr(context, 'field.qty'), child: TextFormField(controller: _qtyController, keyboardType: const TextInputType.numberWithOptions(decimal: true), inputFormatters: [TextInputFormatter.withFunction((oldValue, newValue) { final text = newValue.text; if (text.isEmpty || RegExp(r'^\d*\.?\d{0,2}$').hasMatch(text)) return newValue; return oldValue; })],  decoration: InputDecoration(hintText: test_moduleTr(context, 'field.qty'), border: InputBorder.none, isDense: true), validator: (v) { if (v == null || v.trim().isEmpty) return test_moduleTr(context, 'form.required'); if (v != null && v.isNotEmpty && (double.tryParse(v) == null || !RegExp(r'^\d+(\.\d{1,2})?$').hasMatch(v))) return 'Enter a valid number with up to 2 decimals';  return null; })),
            _FieldShell(label: test_moduleTr(context, 'field.price'), child: TextFormField(controller: _priceController, keyboardType: const TextInputType.numberWithOptions(decimal: true), inputFormatters: [TextInputFormatter.withFunction((oldValue, newValue) { final text = newValue.text; if (text.isEmpty || RegExp(r'^\d*\.?\d{0,2}$').hasMatch(text)) return newValue; return oldValue; })],  decoration: InputDecoration(hintText: test_moduleTr(context, 'field.price'), border: InputBorder.none, isDense: true), validator: (v) { if (v == null || v.trim().isEmpty) return test_moduleTr(context, 'form.required'); if (v != null && v.isNotEmpty && (double.tryParse(v) == null || !RegExp(r'^\d+(\.\d{1,2})?$').hasMatch(v))) return 'Enter a valid number with up to 2 decimals';  return null; })),
            Container(decoration: BoxDecoration(border: Border.all(color: Theme.of(context).colorScheme.outlineVariant), borderRadius: BorderRadius.circular(16)), child: SwitchListTile.adaptive(contentPadding: const EdgeInsets.symmetric(horizontal: 16), title: Text(test_moduleTr(context, 'field.is_active'), style: const TextStyle(fontWeight: FontWeight.w700)), value: _isActive, onChanged: (v) => setState(() => _isActive = v))),
            _FieldShell(label: test_moduleTr(context, 'field.due_date'), child: InkWell(onTap: () => _pickDate(_dueDateController), borderRadius: BorderRadius.circular(12), child: InputDecorator(decoration: InputDecoration(border: InputBorder.none, isDense: true, suffixIcon: const Icon(Icons.calendar_today_rounded)), child: Text(_dueDateController.text.isEmpty ? test_moduleTr(context, 'form.select_date') : _dueDateController.text, style: TextStyle(color: _dueDateController.text.isEmpty ? Theme.of(context).colorScheme.onSurfaceVariant : null, fontWeight: FontWeight.w600))))),
            _FieldShell(label: test_moduleTr(context, 'field.event_at'), child: InkWell(onTap: () => _pickDateTime(_eventAtController), borderRadius: BorderRadius.circular(12), child: InputDecorator(decoration: InputDecoration(border: InputBorder.none, isDense: true, suffixIcon: const Icon(Icons.event_rounded)), child: Text(_eventAtController.text.isEmpty ? test_moduleTr(context, 'form.select_datetime') : _eventAtController.text, style: TextStyle(color: _eventAtController.text.isEmpty ? Theme.of(context).colorScheme.onSurfaceVariant : null, fontWeight: FontWeight.w600))))),
            _FieldShell(label: test_moduleTr(context, 'field.user_id'), child: InkWell(onTap: _pickuserId, borderRadius: BorderRadius.circular(16), child: Container(padding: const EdgeInsets.all(16), decoration: BoxDecoration(border: Border.all(color: Theme.of(context).colorScheme.outlineVariant), borderRadius: BorderRadius.circular(16)), child: Row(children: [Expanded(child: Text(_displayName(_userOptions.firstWhere((e) => e['id'].toString() == _userId?.toString(), orElse: () => {'id': '', 'name': test_moduleTr(context, 'form.select')})))), const Icon(Icons.keyboard_arrow_down_rounded)])))),
            _FieldShell(label: test_moduleTr(context, 'field.priority'), child: DropdownButtonFormField<String>(value: ['Low', 'Medium', 'High'].contains(_priority) ? _priority : null, items: ['Low', 'Medium', 'High'].map((v) => DropdownMenuItem<String>(value: v, child: Text(v))).toList(), onChanged: (v) => setState(() => _priority = v), validator: (v) { if (v == null || v.isEmpty) return test_moduleTr(context, 'form.select'); return null; }, decoration: const InputDecoration(border: InputBorder.none, isDense: true))),
            PremiumImagePicker(label: test_moduleTr(context, 'field.image'), path: _imagePath, currentUrl: widget.item?['image'] != null ? '${ApiService.serverUrl}/${widget.item!['image']}' : null, onPicked: (p) => setState(() => _imagePath = p)),
            PremiumImagePicker(label: test_moduleTr(context, 'field.cover_photo'), path: _coverPhotoPath, currentUrl: widget.item?['cover_photo'] != null ? '${ApiService.serverUrl}/${widget.item!['cover_photo']}' : null, onPicked: (p) => setState(() => _coverPhotoPath = p)),

            const SizedBox(height: 14),
            BlocBuilder<TestModuleCubit, TestModuleState>(builder: (context, state) => PremiumButton(onPressed: () => _save(context), label: state is TestModuleSaving ? test_moduleTr(context, 'form.saving') : test_moduleTr(context, 'form.save'), icon: Icons.check_rounded, loading: state is TestModuleSaving, expand: true)),
          ]))),
        ),
      ),
    );
  }

  @override void dispose() {
    _nameController.dispose();
    _descriptionController.dispose();
    _qtyController.dispose();
    _priceController.dispose();
    _dueDateController.dispose();
    _eventAtController.dispose();
    super.dispose();
  }
}
class _FormHeader extends StatelessWidget {
  final bool isEdit;
  const _FormHeader({required this.isEdit});
  @override Widget build(BuildContext context) => Container(padding: const EdgeInsets.all(20), decoration: BoxDecoration(gradient: LinearGradient(colors: [Theme.of(context).colorScheme.primaryContainer, Theme.of(context).colorScheme.secondaryContainer]), borderRadius: BorderRadius.circular(24)), child: Row(children: [Container(width: 48, height: 48, decoration: BoxDecoration(color: Theme.of(context).colorScheme.surface.withOpacity(.7), borderRadius: BorderRadius.circular(15)), child: Icon(isEdit ? Icons.edit_rounded : Icons.add_rounded)), const SizedBox(width: 14), Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(isEdit ? test_moduleTr(context, 'form.update_record') : test_moduleTr(context, 'form.new_record'), style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900)), const SizedBox(height: 3), Text(isEdit ? test_moduleTr(context, 'form.review_update') : test_moduleTr(context, 'form.fill_create'), style: TextStyle(fontSize: 12, color: Theme.of(context).colorScheme.onSurfaceVariant))]))]));
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