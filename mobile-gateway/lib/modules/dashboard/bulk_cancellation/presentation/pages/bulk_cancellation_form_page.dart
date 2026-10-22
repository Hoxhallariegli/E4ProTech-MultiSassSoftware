import 'dart:async';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:mobile_gateway/core/widgets/premium_widgets.dart';
import 'package:mobile_gateway/l10n/bulk_cancellation_localization.dart';
import '../cubit/bulk_cancellation_cubit.dart';
import '../cubit/bulk_cancellation_state.dart';
import '../../data/bulk_cancellation_repository.dart';
import 'package:mobile_gateway/services/api_service.dart';
import 'package:mobile_gateway/services/auth_service.dart';

class BulkCancellationFormPage extends StatefulWidget {
  final Map<String, dynamic>? item;
  const BulkCancellationFormPage({super.key, this.item});
  @override State<BulkCancellationFormPage> createState() => _BulkCancellationFormPageState();
}

class _BulkCancellationFormPageState extends State<BulkCancellationFormPage> {
  final _formKey = GlobalKey<FormState>();
  final repository = BulkCancellationRepository();
  bool _loading = true;

  final _startAtController = TextEditingController();
  final _endAtController = TextEditingController();
  final _reasonController = TextEditingController();

  List<Map<String, dynamic>> _barberShopOptions = [];
  int? _barberShopId;
  List<Map<String, dynamic>> _barberOptions = [];
  int? _barberId;

  int _previewAffectedCount = 0;
  bool _checkingPreview = false;
  Timer? _previewDebounce;

  @override void initState() { super.initState(); _init(); }

  Future<void> _init() async {
    final now = DateTime.now();
    _startAtController.text = _displayDateTime(widget.item?['start_at'] ?? now, includeTime: true);
    _endAtController.text = _displayDateTime(widget.item?['end_at'] ?? now.add(const Duration(hours: 6)), includeTime: true);
    _reasonController.text = widget.item?['reason']?.toString() ?? '';

    try {
      _barberShopOptions = await repository.lookup('barber-shops');
      if (widget.item?['barber_shop_id'] != null) {
        _barberShopId = int.tryParse(widget.item!['barber_shop_id'].toString());
      }
      if (_barberShopId == null && AuthService.instance.user?['is_admin'] != true) {
        _barberShopId = AuthService.instance.user?['barber_shop_id'] as int?;
      }

      _barberOptions = await repository.lookup('barbers');
      if (widget.item?['barber_id'] != null) {
        _barberId = int.tryParse(widget.item!['barber_id'].toString());
      } else if (_barberOptions.isNotEmpty) {
        _barberId = int.tryParse(_barberOptions.first['id']?.toString() ?? '');
      }

      _triggerPreviewCheck();
    } catch (_) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(bulk_cancellationTr(context, 'form.could_not_load')), behavior: SnackBarBehavior.floating),
        );
      }
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  void _triggerPreviewCheck() {
    _previewDebounce?.cancel();
    _previewDebounce = Timer(const Duration(milliseconds: 300), _calculateAffectedPreview);
  }

  Future<void> _calculateAffectedPreview() async {
    if (_barberId == null || _startAtController.text.isEmpty || _endAtController.text.isEmpty) {
      if (mounted) setState(() => _previewAffectedCount = 0);
      return;
    }

    final startIso = _apiDateTimeValue(_startAtController.text);
    final endIso = _apiDateTimeValue(_endAtController.text);

    if (startIso == null || endIso == null) return;

    if (mounted) setState(() => _checkingPreview = true);

    try {
      final res = await ApiService.get('/bookings?barber_id=$_barberId&per_page=100');
      if (res.statusCode >= 200 && res.statusCode < 300) {
        final decoded = jsonDecode(res.body);
        final list = (decoded['data'] as List? ?? []);
        final startDt = DateTime.parse(startIso);
        final endDt = DateTime.parse(endIso);

        int count = 0;
        for (var b in list) {
          final status = (b['status'] ?? '').toString();
          if (status == 'pending' || status == 'confirmed') {
            final appAt = DateTime.tryParse((b['appointment_at'] ?? '').toString());
            if (appAt != null && appAt.isAfter(startDt.subtract(const Duration(seconds: 1))) && appAt.isBefore(endDt.add(const Duration(seconds: 1)))) {
              count++;
            }
          }
        }
        if (mounted) setState(() => _previewAffectedCount = count);
      }
    } catch (_) {
    } finally {
      if (mounted) setState(() => _checkingPreview = false);
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
        const SizedBox(height: 18), Align(alignment: Alignment.centerLeft, child: Text(bulk_cancellationTr(sheetContext, 'field.barber_shop_id'), style: const TextStyle(fontSize: 21, fontWeight: FontWeight.w800))),
        const SizedBox(height: 14), TextField(decoration: InputDecoration(prefixIcon: const Icon(Icons.search_rounded), hintText: bulk_cancellationTr(sheetContext, 'form.search'), border: const OutlineInputBorder()), onChanged: (q) => setSheet(() => filtered = _barberShopOptions.where((e) => _displayName(e).toLowerCase().contains(q.toLowerCase())).toList())),
        const SizedBox(height: 12), Expanded(child: ListView.separated(itemCount: filtered.length, separatorBuilder: (_, __) => const Divider(height: 1), itemBuilder: (_, i) { final option = filtered[i]; return ListTile(title: Text(_displayName(option), style: const TextStyle(fontWeight: FontWeight.w700)), trailing: option['id'].toString() == _barberShopId?.toString() ? const Icon(Icons.check_circle_rounded) : null, onTap: () => Navigator.pop(sheetContext, option)); })),
      ])))),
    );
    if (selected != null) {
      setState(() => _barberShopId = int.tryParse(selected['id'].toString()));
      _triggerPreviewCheck();
    }
  }

  Future<void> _pickbarberId() async {
    var filtered = List<Map<String, dynamic>>.from(_barberOptions);
    final selected = await showModalBottomSheet<Map<String, dynamic>>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Theme.of(context).colorScheme.surface,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(30))),
      builder: (sheetContext) => StatefulBuilder(builder: (context, setSheet) => SizedBox(height: MediaQuery.of(context).size.height * .72, child: Padding(padding: const EdgeInsets.all(20), child: Column(children: [
        Container(width: 42, height: 4, decoration: BoxDecoration(color: Theme.of(context).dividerColor, borderRadius: BorderRadius.circular(10))),
        const SizedBox(height: 18), Align(alignment: Alignment.centerLeft, child: Text(bulk_cancellationTr(sheetContext, 'field.barber_id'), style: const TextStyle(fontSize: 21, fontWeight: FontWeight.w800))),
        const SizedBox(height: 14), TextField(decoration: InputDecoration(prefixIcon: const Icon(Icons.search_rounded), hintText: bulk_cancellationTr(sheetContext, 'form.search'), border: const OutlineInputBorder()), onChanged: (q) => setSheet(() => filtered = _barberOptions.where((e) => _displayName(e).toLowerCase().contains(q.toLowerCase())).toList())),
        const SizedBox(height: 12), Expanded(child: ListView.separated(itemCount: filtered.length, separatorBuilder: (_, __) => const Divider(height: 1), itemBuilder: (_, i) { final option = filtered[i]; return ListTile(title: Text(_displayName(option), style: const TextStyle(fontWeight: FontWeight.w700)), trailing: option['id'].toString() == _barberId?.toString() ? const Icon(Icons.check_circle_rounded) : null, onTap: () => Navigator.pop(sheetContext, option)); })),
      ])))),
    );
    if (selected != null) {
      setState(() => _barberId = int.tryParse(selected['id'].toString()));
      _triggerPreviewCheck();
    }
  }

  Future<void> _pickDateTime(TextEditingController controller) async {
    final initial = _parseDisplayDate(controller.text) ?? DateTime.now();
    final date = await showDatePicker(context: context, initialDate: initial, firstDate: DateTime(1900), lastDate: DateTime(2200));
    if (date == null || !mounted) return;
    final time = await showTimePicker(context: context, initialTime: TimeOfDay.fromDateTime(initial));
    if (time == null || !mounted) return;
    final value = DateTime(date.year, date.month, date.day, time.hour, time.minute);
    setState(() => controller.text = _formatDisplayDate(value, includeTime: true));
    _triggerPreviewCheck();
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
    payload['barber_id'] = _barberId;
    payload['start_at'] = _startAtController.text.isEmpty ? null : _apiDateTimeValue(_startAtController.text);
    payload['end_at'] = _endAtController.text.isEmpty ? null : _apiDateTimeValue(_endAtController.text);
    payload['reason'] = _reasonController.text.isEmpty ? 'Mungesë emergjente stafi' : _reasonController.text;

    context.read<BulkCancellationCubit>().save(payload, id: widget.item?['id']);
  }

  @override Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;

    return BlocProvider(
      create: (_) => BulkCancellationCubit(repository),
      child: BlocListener<BulkCancellationCubit, BulkCancellationState>(
        listener: (context, state) {
          if (state is BulkCancellationSaved) Navigator.pop(context, true);
          if (state is BulkCancellationFailure) {
            final message = state.message.trim().isEmpty ? bulk_cancellationTr(context, 'form.save_error') : state.message;
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
            title: Text(widget.item == null ? bulk_cancellationTr(context, 'form.create_title') : bulk_cancellationTr(context, 'form.edit_title'), style: const TextStyle(fontWeight: FontWeight.w800)),
          ),
          body: _loading
              ? const Center(child: CircularProgressIndicator.adaptive())
              : Form(
                  key: _formKey,
                  child: Builder(
                    builder: (formContext) => ListView(
                      padding: const EdgeInsets.fromLTRB(20, 12, 20, 120),
                      children: [
                        _FormHeader(isEdit: widget.item != null),
                        const SizedBox(height: 22),

                        if (AuthService.instance.user?['is_admin'] == true)
                          _FieldShell(
                            label: bulk_cancellationTr(context, 'field.barber_shop_id'),
                            child: InkWell(
                              onTap: _pickbarberShopId,
                              borderRadius: BorderRadius.circular(16),
                              child: Container(
                                padding: const EdgeInsets.all(16),
                                decoration: BoxDecoration(border: Border.all(color: Theme.of(context).colorScheme.outlineVariant), borderRadius: BorderRadius.circular(16)),
                                child: Row(children: [
                                  Expanded(child: Text(_displayName(_barberShopOptions.firstWhere((e) => e['id'].toString() == _barberShopId?.toString(), orElse: () => {'id': '', 'name': bulk_cancellationTr(context, 'form.select')})))) ,
                                  const Icon(Icons.keyboard_arrow_down_rounded),
                                ]),
                              ),
                            ),
                          )
                        else
                          _FieldShell(
                            label: bulk_cancellationTr(context, 'field.barber_shop_id'),
                            child: Container(
                              padding: const EdgeInsets.all(16),
                              width: double.infinity,
                              decoration: BoxDecoration(color: theme.colorScheme.surfaceContainerHighest.withOpacity(0.5), border: Border.all(color: theme.colorScheme.outlineVariant), borderRadius: BorderRadius.circular(16)),
                              child: Text(AuthService.instance.user?['business']?['name'] ?? '', style: const TextStyle(fontWeight: FontWeight.bold)),
                            ),
                          ),

                        _FieldShell(
                          label: bulk_cancellationTr(context, 'field.barber_id'),
                          child: InkWell(
                            onTap: _pickbarberId,
                            borderRadius: BorderRadius.circular(16),
                            child: Container(
                              padding: const EdgeInsets.all(16),
                              decoration: BoxDecoration(border: Border.all(color: Theme.of(context).colorScheme.outlineVariant), borderRadius: BorderRadius.circular(16)),
                              child: Row(children: [
                                Expanded(child: Text(_displayName(_barberOptions.firstWhere((e) => e['id'].toString() == _barberId?.toString(), orElse: () => {'id': '', 'name': bulk_cancellationTr(context, 'form.select')})))) ,
                                const Icon(Icons.keyboard_arrow_down_rounded),
                              ]),
                            ),
                          ),
                        ),

                        _FieldShell(
                          label: bulk_cancellationTr(context, 'field.start_at'),
                          child: InkWell(
                            onTap: () => _pickDateTime(_startAtController),
                            borderRadius: BorderRadius.circular(12),
                            child: InputDecorator(
                              decoration: const InputDecoration(border: InputBorder.none, isDense: true, suffixIcon: Icon(Icons.event_rounded)),
                              child: Text(_startAtController.text.isEmpty ? bulk_cancellationTr(context, 'form.select_datetime') : _startAtController.text, style: TextStyle(color: _startAtController.text.isEmpty ? Theme.of(context).colorScheme.onSurfaceVariant : null, fontWeight: FontWeight.w600)),
                            ),
                          ),
                        ),

                        _FieldShell(
                          label: bulk_cancellationTr(context, 'field.end_at'),
                          child: InkWell(
                            onTap: () => _pickDateTime(_endAtController),
                            borderRadius: BorderRadius.circular(12),
                            child: InputDecorator(
                              decoration: const InputDecoration(border: InputBorder.none, isDense: true, suffixIcon: Icon(Icons.event_rounded)),
                              child: Text(_endAtController.text.isEmpty ? bulk_cancellationTr(context, 'form.select_datetime') : _endAtController.text, style: TextStyle(color: _endAtController.text.isEmpty ? Theme.of(context).colorScheme.onSurfaceVariant : null, fontWeight: FontWeight.w600)),
                            ),
                          ),
                        ),

                        _FieldShell(
                          label: bulk_cancellationTr(context, 'field.reason'),
                          child: TextFormField(
                            controller: _reasonController,
                            decoration: const InputDecoration(
                              hintText: 'P.sh. Emergjencë Familjare / Ndryshim Orari',
                              border: InputBorder.none,
                              isDense: true,
                            ),
                          ),
                        ),

                        // LIVE PREVIEW CARD OF AFFECTED BOOKINGS COUNT
                        const SizedBox(height: 8),
                        Container(
                          padding: const EdgeInsets.all(16),
                          decoration: BoxDecoration(
                            color: Colors.amber.withOpacity(0.12),
                            borderRadius: BorderRadius.circular(20),
                            border: Border.all(color: Colors.amber.withOpacity(0.4)),
                          ),
                          child: Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.all(10),
                                decoration: BoxDecoration(
                                  color: Colors.amber.withOpacity(0.2),
                                  shape: BoxShape.circle,
                                ),
                                child: const Icon(Icons.warning_amber_rounded, color: Colors.amber, size: 24),
                              ),
                              const SizedBox(width: 14),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    _checkingPreview
                                        ? const Text('Skanim i takimeve te prekura...', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13))
                                        : Text(
                                            '$_previewAffectedCount Takime do të Anulohen',
                                            style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 15, color: Colors.amber),
                                          ),
                                    const SizedBox(height: 2),
                                    const Text(
                                      'SMS-të do të dërgohen me radhë (1 SMS/minutë) për çdo klient.',
                                      style: TextStyle(fontSize: 11, color: Colors.grey),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        ),

                        const SizedBox(height: 22),
                        BlocBuilder<BulkCancellationCubit, BulkCancellationState>(
                          builder: (context, state) => PremiumButton(
                            onPressed: () => _save(context),
                            label: state is BulkCancellationSaving ? bulk_cancellationTr(context, 'form.saving') : bulk_cancellationTr(context, 'form.save'),
                            icon: Icons.check_circle_outline_rounded,
                            loading: state is BulkCancellationSaving,
                            expand: true,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
        ),
      ),
    );
  }

  @override void dispose() {
    _previewDebounce?.cancel();
    _startAtController.dispose();
    _endAtController.dispose();
    _reasonController.dispose();
    super.dispose();
  }
}

class _FormHeader extends StatelessWidget {
  final bool isEdit;
  const _FormHeader({required this.isEdit});
  @override Widget build(BuildContext context) => Container(padding: const EdgeInsets.all(20), decoration: BoxDecoration(gradient: LinearGradient(colors: [Theme.of(context).colorScheme.primaryContainer, Theme.of(context).colorScheme.secondaryContainer]), borderRadius: BorderRadius.circular(24)), child: Row(children: [Container(width: 48, height: 48, decoration: BoxDecoration(color: Theme.of(context).colorScheme.surface.withOpacity(.7), borderRadius: BorderRadius.circular(15)), child: Icon(isEdit ? Icons.edit_rounded : Icons.add_rounded)), const SizedBox(width: 14), Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(isEdit ? bulk_cancellationTr(context, 'form.update_record') : bulk_cancellationTr(context, 'form.new_record'), style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900)), const SizedBox(height: 3), Text(isEdit ? bulk_cancellationTr(context, 'form.review_update') : bulk_cancellationTr(context, 'form.fill_create'), style: TextStyle(fontSize: 12, color: Theme.of(context).colorScheme.onSurfaceVariant))]))]));
}

class _FieldShell extends StatelessWidget {
  final String label; final Widget child;
  const _FieldShell({required this.label, required this.child});
  @override Widget build(BuildContext context) => Container(margin: const EdgeInsets.only(bottom: 14), padding: const EdgeInsets.fromLTRB(16, 12, 16, 6), decoration: BoxDecoration(color: Theme.of(context).colorScheme.surfaceContainerHighest.withOpacity(.35), border: Border.all(color: Theme.of(context).colorScheme.outlineVariant.withOpacity(.7)), borderRadius: BorderRadius.circular(18)), child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(label, style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: Theme.of(context).colorScheme.onSurfaceVariant)), child]));
}
