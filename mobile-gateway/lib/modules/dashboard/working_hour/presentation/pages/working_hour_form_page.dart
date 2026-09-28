import 'dart:convert';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:image_picker/image_picker.dart';
import 'package:mobile_gateway/core/widgets/premium_widgets.dart';
import 'package:mobile_gateway/core/widgets/premium_image_picker.dart';
import 'package:mobile_gateway/l10n/working_hour_localization.dart';
import '../cubit/working_hour_cubit.dart';
import '../cubit/working_hour_state.dart';
import '../../data/working_hour_repository.dart';
import 'package:mobile_gateway/services/api_service.dart';
import 'package:mobile_gateway/services/auth_service.dart';

class WorkingHourFormPage extends StatefulWidget {
  final Map<String, dynamic>? item;
  const WorkingHourFormPage({super.key, this.item});
  @override State<WorkingHourFormPage> createState() => _WorkingHourFormPageState();
}

class _WorkingHourFormPageState extends State<WorkingHourFormPage> {
  final _formKey = GlobalKey<FormState>();
  final repository = WorkingHourRepository();
  bool _loading = true;

  String? _dayOfWeek;
  final _openTimeController = TextEditingController();
  final _closeTimeController = TextEditingController();
  final _lunchStartController = TextEditingController();
  final _lunchEndController = TextEditingController();
  bool _isClosed = false;

  List<Map<String, dynamic>> _barberOptions = [];
  int? _barberId;


  @override void initState() { super.initState(); _init(); }

  Future<void> _init() async {
    _dayOfWeek = widget.item?['day_of_week']?.toString();
    _openTimeController.text = widget.item?['open_time']?.toString() ?? '';
    _closeTimeController.text = widget.item?['close_time']?.toString() ?? '';
    _lunchStartController.text = widget.item?['lunch_start']?.toString() ?? '';
    _lunchEndController.text = widget.item?['lunch_end']?.toString() ?? '';
    _isClosed = widget.item?['is_closed'] == true || widget.item?['is_closed'] == 1 || widget.item?['is_closed'] == '1';

    try {
    _barberOptions = await repository.lookup('barbers');
    if (widget.item?['barber_id'] != null) _barberId = int.tryParse(widget.item!['barber_id'].toString());
    } catch (_) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(working_hourTr(context, 'form.could_not_load')), behavior: SnackBarBehavior.floating));
    } finally { if (mounted) setState(() => _loading = false); }
  }

  Future<void> _pickbarberId() async {
    if ("barber_id" == "barber_shop_id" && AuthService.instance.user?['is_admin'] != true) return;
    var filtered = List<Map<String, dynamic>>.from(_barberOptions);
    final selected = await showModalBottomSheet<Map<String, dynamic>>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Theme.of(context).colorScheme.surface,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(30))),
      builder: (sheetContext) => StatefulBuilder(builder: (context, setSheet) => SizedBox(height: MediaQuery.of(context).size.height * .72, child: Padding(padding: const EdgeInsets.all(20), child: Column(children: [
        Container(width: 42, height: 4, decoration: BoxDecoration(color: Theme.of(context).dividerColor, borderRadius: BorderRadius.circular(10))),
        const SizedBox(height: 18), Align(alignment: Alignment.centerLeft, child: Text(working_hourTr(sheetContext, 'field.barber_id'), style: const TextStyle(fontSize: 21, fontWeight: FontWeight.w800))),
        const SizedBox(height: 14), TextField(decoration: InputDecoration(prefixIcon: const Icon(Icons.search_rounded), hintText: working_hourTr(sheetContext, 'form.search'), border: const OutlineInputBorder()), onChanged: (q) => setSheet(() => filtered = _barberOptions.where((e) => _displayName(e).toLowerCase().contains(q.toLowerCase())).toList())),
        const SizedBox(height: 12), Expanded(child: ListView.separated(itemCount: filtered.length, separatorBuilder: (_, __) => const Divider(height: 1), itemBuilder: (_, i) { final option = filtered[i]; return ListTile(title: Text(_displayName(option), style: const TextStyle(fontWeight: FontWeight.w700)), trailing: option['id'].toString() == _barberId?.toString() ? const Icon(Icons.check_circle_rounded) : null, onTap: () => Navigator.pop(sheetContext, option)); })),
      ])))),
    );
    if (selected != null) setState(() => _barberId = int.tryParse(selected['id'].toString()));
  }

  Future<void> _pickTimeOnly(TextEditingController controller) async {
    final parts = controller.text.split(':');
    final initialHour = int.tryParse(parts[0]) ?? 12;
    final initialMinute = parts.length > 1 ? (int.tryParse(parts[1]) ?? 0) : 0;
    final time = await showTimePicker(context: context, initialTime: TimeOfDay(hour: initialHour, minute: initialMinute));
    if (time != null && mounted) {
      final hour = time.hour.toString().padLeft(2, '0');
      final minute = time.minute.toString().padLeft(2, '0');
      setState(() => controller.text = '$hour:$minute');
    }
  }

  String _displayName(Map<String, dynamic> item) {
    return (item['name'] ?? item['title'] ?? 'ID: ' + item['id'].toString()).toString();
  }

  Future<void> _save(BuildContext context) async {
    if (!_formKey.currentState!.validate()) return;
    final payload = <String, dynamic>{};
    payload['barber_id'] = _barberId;
    payload['day_of_week'] = _dayOfWeek;
    payload['open_time'] = _openTimeController.text.trim().isEmpty ? null : _openTimeController.text.trim();
    payload['close_time'] = _closeTimeController.text.trim().isEmpty ? null : _closeTimeController.text.trim();
    payload['lunch_start'] = _lunchStartController.text.trim().isEmpty ? null : _lunchStartController.text.trim();
    payload['lunch_end'] = _lunchEndController.text.trim().isEmpty ? null : _lunchEndController.text.trim();
    payload['is_closed'] = _isClosed;

    context.read<WorkingHourCubit>().save(payload, id: widget.item?['id']);
  }

  @override Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return BlocProvider(
      create: (_) => WorkingHourCubit(repository),
      child: BlocListener<WorkingHourCubit, WorkingHourState>(
        listener: (context, state) {
          if (state is WorkingHourSaved) Navigator.pop(context, true);
          if (state is WorkingHourFailure) {
            final message = state.message.trim().isEmpty ? working_hourTr(context, 'form.save_error') : state.message;
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
            title: Text(widget.item == null ? working_hourTr(context, 'form.create_title') : working_hourTr(context, 'form.edit_title'), style: const TextStyle(fontWeight: FontWeight.w800)),
          ),
          body: _loading ? const Center(child: CircularProgressIndicator.adaptive()) : Form(key: _formKey, child: Builder(builder: (formContext) => ListView(padding: const EdgeInsets.fromLTRB(20, 12, 20, 120), children: [
            const SizedBox(height: 12),
            _FieldShell(label: working_hourTr(context, 'field.barber_id'), child: InkWell(onTap: _pickbarberId, borderRadius: BorderRadius.circular(16), child: Container(padding: const EdgeInsets.all(16), decoration: BoxDecoration(border: Border.all(color: Theme.of(context).colorScheme.outlineVariant), borderRadius: BorderRadius.circular(16)), child: Row(children: [Expanded(child: Text(_displayName(_barberOptions.firstWhere((e) => e['id'].toString() == _barberId?.toString(), orElse: () => {'id': '', 'name': working_hourTr(context, 'form.select')})))), const Icon(Icons.keyboard_arrow_down_rounded)])))),
            _FieldShell(label: working_hourTr(context, 'field.day_of_week'), child: DropdownButtonFormField<String>(value: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'].contains(_dayOfWeek) ? _dayOfWeek : null, items: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'].map((v) => DropdownMenuItem<String>(value: v, child: Text(v))).toList(), onChanged: (v) => setState(() => _dayOfWeek = v), validator: (v) { if (v == null || v.isEmpty) return working_hourTr(context, 'form.select'); return null; }, decoration: const InputDecoration(border: InputBorder.none, isDense: true))),
            _FieldShell(label: working_hourTr(context, 'field.open_time'), child: InkWell(onTap: () => _pickTimeOnly(_openTimeController), borderRadius: BorderRadius.circular(12), child: InputDecorator(decoration: const InputDecoration(border: InputBorder.none, isDense: true, suffixIcon: Icon(Icons.access_time_rounded)), child: Text(_openTimeController.text.isEmpty ? '08:00' : _openTimeController.text, style: TextStyle(color: _openTimeController.text.isEmpty ? Theme.of(context).colorScheme.onSurfaceVariant : null, fontWeight: FontWeight.w600))))),
            _FieldShell(label: working_hourTr(context, 'field.close_time'), child: InkWell(onTap: () => _pickTimeOnly(_closeTimeController), borderRadius: BorderRadius.circular(12), child: InputDecorator(decoration: const InputDecoration(border: InputBorder.none, isDense: true, suffixIcon: Icon(Icons.access_time_rounded)), child: Text(_closeTimeController.text.isEmpty ? '20:00' : _closeTimeController.text, style: TextStyle(color: _closeTimeController.text.isEmpty ? Theme.of(context).colorScheme.onSurfaceVariant : null, fontWeight: FontWeight.w600))))),
            _FieldShell(label: working_hourTr(context, 'field.lunch_start'), child: InkWell(onTap: () => _pickTimeOnly(_lunchStartController), borderRadius: BorderRadius.circular(12), child: InputDecorator(decoration: const InputDecoration(border: InputBorder.none, isDense: true, suffixIcon: Icon(Icons.restaurant_rounded)), child: Text(_lunchStartController.text.isEmpty ? 'Kjo ditë pa pushim dreke' : _lunchStartController.text, style: TextStyle(color: _lunchStartController.text.isEmpty ? Theme.of(context).colorScheme.onSurfaceVariant : null, fontWeight: FontWeight.w600))))),
            _FieldShell(label: working_hourTr(context, 'field.lunch_end'), child: InkWell(onTap: () => _pickTimeOnly(_lunchEndController), borderRadius: BorderRadius.circular(12), child: InputDecorator(decoration: const InputDecoration(border: InputBorder.none, isDense: true, suffixIcon: Icon(Icons.restaurant_rounded)), child: Text(_lunchEndController.text.isEmpty ? 'Kjo ditë pa pushim dreke' : _lunchEndController.text, style: TextStyle(color: _lunchEndController.text.isEmpty ? Theme.of(context).colorScheme.onSurfaceVariant : null, fontWeight: FontWeight.w600))))),
            Container(decoration: BoxDecoration(border: Border.all(color: Theme.of(context).colorScheme.outlineVariant), borderRadius: BorderRadius.circular(16)), child: SwitchListTile.adaptive(contentPadding: const EdgeInsets.symmetric(horizontal: 16), title: Text(working_hourTr(context, 'field.is_closed'), style: const TextStyle(fontWeight: FontWeight.w700)), value: _isClosed, onChanged: (v) => setState(() => _isClosed = v))),

            const SizedBox(height: 14),
            BlocBuilder<WorkingHourCubit, WorkingHourState>(builder: (context, state) => PremiumButton(onPressed: () => _save(context), label: state is WorkingHourSaving ? working_hourTr(context, 'form.saving') : working_hourTr(context, 'form.save'), icon: Icons.check_rounded, loading: state is WorkingHourSaving, expand: true)),
          ]))),
        ),
      ),
    );
  }

  @override void dispose() {
    _openTimeController.dispose();
    _closeTimeController.dispose();
    super.dispose();
  }
}
class _FormHeader extends StatelessWidget {
  final bool isEdit;
  const _FormHeader({required this.isEdit});
  @override Widget build(BuildContext context) => Container(padding: const EdgeInsets.all(20), decoration: BoxDecoration(gradient: LinearGradient(colors: [Theme.of(context).colorScheme.primaryContainer, Theme.of(context).colorScheme.secondaryContainer]), borderRadius: BorderRadius.circular(24)), child: Row(children: [Container(width: 48, height: 48, decoration: BoxDecoration(color: Theme.of(context).colorScheme.surface.withOpacity(.7), borderRadius: BorderRadius.circular(15)), child: Icon(isEdit ? Icons.edit_rounded : Icons.add_rounded)), const SizedBox(width: 14), Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(isEdit ? working_hourTr(context, 'form.update_record') : working_hourTr(context, 'form.new_record'), style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900)), const SizedBox(height: 3), Text(isEdit ? working_hourTr(context, 'form.review_update') : working_hourTr(context, 'form.fill_create'), style: TextStyle(fontSize: 12, color: Theme.of(context).colorScheme.onSurfaceVariant))]))]));
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
