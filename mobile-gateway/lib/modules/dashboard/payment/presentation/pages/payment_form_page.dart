import 'dart:convert';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:image_picker/image_picker.dart';
import 'package:mobile_gateway/core/widgets/premium_widgets.dart';
import 'package:mobile_gateway/core/widgets/premium_image_picker.dart';
import 'package:mobile_gateway/l10n/payment_localization.dart';
import '../cubit/payment_cubit.dart';
import '../cubit/payment_state.dart';
import '../../data/payment_repository.dart';
import 'package:mobile_gateway/services/api_service.dart';
import 'package:mobile_gateway/services/auth_service.dart';

class PaymentFormPage extends StatefulWidget {
  final Map<String, dynamic>? item;
  const PaymentFormPage({super.key, this.item});
  @override State<PaymentFormPage> createState() => _PaymentFormPageState();
}

class _PaymentFormPageState extends State<PaymentFormPage> {
  final _formKey = GlobalKey<FormState>();
  final repository = PaymentRepository();
  bool _loading = true;

  final _amountController = TextEditingController();
  String? _method;
  String? _status;

  List<Map<String, dynamic>> _barberShopOptions = [];
  int? _barberShopId;
  List<Map<String, dynamic>> _bookingOptions = [];
  int? _bookingId;


  @override void initState() { super.initState(); _init(); }

  Future<void> _init() async {
    final initialAmount = widget.item?['amount'] ?? widget.item?['total_price'];
    _amountController.text = (initialAmount != null && double.tryParse(initialAmount.toString()) != null)
        ? double.parse(initialAmount.toString()).toStringAsFixed(2)
        : (initialAmount?.toString() ?? '');
    _method = widget.item?['method']?.toString() ?? 'cash';
    final rawStatus = widget.item?['status']?.toString();
    _status = (rawStatus != null && ['pending', 'paid', 'refunded', 'failed'].contains(rawStatus))
        ? rawStatus
        : 'paid';

    if (widget.item?['barber_shop_id'] != null) {
      _barberShopId = int.tryParse(widget.item!['barber_shop_id'].toString());
    }
    if (_barberShopId == null && AuthService.instance.user?['is_admin'] != true) {
      _barberShopId = AuthService.instance.user?['barber_shop_id'] as int?;
    }

    final rawBookingId = widget.item?['booking_id'] ?? widget.item?['id'];
    if (rawBookingId != null) {
      _bookingId = int.tryParse(rawBookingId.toString());
    }

    try {
      if (AuthService.instance.user?['is_admin'] == true) {
        _barberShopOptions = await repository.lookup('barber-shops');
      }
    } catch (_) {}

    try {
      _bookingOptions = await repository.lookup('bookings');
    } catch (_) {}

    if (_bookingId != null) {
      final custName = widget.item?['customer_name'] ?? widget.item?['customer']?['name'] ?? 'Klient';
      final servName = widget.item?['service_name'] ?? widget.item?['service']?['name'] ?? '';
      final price = widget.item?['amount'] ?? widget.item?['total_price'];
      final formattedTitle = "Takimi #$_bookingId - $custName ${servName.isNotEmpty ? '($servName)' : ''}".trim();

      final index = _bookingOptions.indexWhere((b) => b['id'].toString() == _bookingId.toString());
      if (index != -1) {
        _bookingOptions[index]['name'] = formattedTitle;
        if (price != null) {
          _bookingOptions[index]['amount'] = price;
        }
      } else {
        _bookingOptions.insert(0, {
          'id': _bookingId,
          'name': formattedTitle,
          'amount': price,
          'barber_shop_id': _barberShopId,
        });
      }

      if (_amountController.text.isEmpty || _amountController.text == '0' || _amountController.text == '0.00') {
        final foundAmt = price ?? _bookingOptions.firstWhere((b) => b['id'].toString() == _bookingId.toString(), orElse: () => {})['amount'];
        if (foundAmt != null) {
          final parsed = double.tryParse(foundAmt.toString());
          if (parsed != null && parsed > 0) {
            _amountController.text = parsed.toStringAsFixed(2);
          }
        }
      }
    }

    if (mounted) setState(() => _loading = false);
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
        const SizedBox(height: 18), Align(alignment: Alignment.centerLeft, child: Text(paymentTr(sheetContext, 'field.barber_shop_id'), style: const TextStyle(fontSize: 21, fontWeight: FontWeight.w800))),
        const SizedBox(height: 14), TextField(decoration: InputDecoration(prefixIcon: const Icon(Icons.search_rounded), hintText: paymentTr(sheetContext, 'form.search'), border: const OutlineInputBorder()), onChanged: (q) => setSheet(() => filtered = _barberShopOptions.where((e) => _displayName(e).toLowerCase().contains(q.toLowerCase())).toList())),
        const SizedBox(height: 12), Expanded(child: ListView.separated(itemCount: filtered.length, separatorBuilder: (_, __) => const Divider(height: 1), itemBuilder: (_, i) { final option = filtered[i]; return ListTile(title: Text(_displayName(option), style: const TextStyle(fontWeight: FontWeight.w700)), trailing: option['id'].toString() == _barberShopId?.toString() ? const Icon(Icons.check_circle_rounded) : null, onTap: () => Navigator.pop(sheetContext, option)); })),
      ])))),
    );
    if (selected != null) setState(() => _barberShopId = int.tryParse(selected['id'].toString()));
  }  Future<void> _pickbookingId() async {
    var filtered = List<Map<String, dynamic>>.from(_bookingOptions);
    final selected = await showModalBottomSheet<Map<String, dynamic>>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Theme.of(context).colorScheme.surface,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(30))),
      builder: (sheetContext) => StatefulBuilder(builder: (context, setSheet) => SizedBox(height: MediaQuery.of(context).size.height * .72, child: Padding(padding: const EdgeInsets.all(20), child: Column(children: [
        Container(width: 42, height: 4, decoration: BoxDecoration(color: Theme.of(context).dividerColor, borderRadius: BorderRadius.circular(10))),
        const SizedBox(height: 18), Align(alignment: Alignment.centerLeft, child: Text(paymentTr(sheetContext, 'field.booking_id'), style: const TextStyle(fontSize: 21, fontWeight: FontWeight.w800))),
        const SizedBox(height: 14), TextField(decoration: InputDecoration(prefixIcon: const Icon(Icons.search_rounded), hintText: paymentTr(sheetContext, 'form.search'), border: const OutlineInputBorder()), onChanged: (q) => setSheet(() => filtered = _bookingOptions.where((e) => _displayName(e).toLowerCase().contains(q.toLowerCase())).toList())),
        const SizedBox(height: 12), Expanded(child: ListView.separated(itemCount: filtered.length, separatorBuilder: (_, __) => const Divider(height: 1), itemBuilder: (_, i) { final option = filtered[i]; return ListTile(title: Text(_displayName(option), style: const TextStyle(fontWeight: FontWeight.w700)), trailing: option['id'].toString() == _bookingId?.toString() ? const Icon(Icons.check_circle_rounded) : null, onTap: () => Navigator.pop(sheetContext, option)); })),
      ])))),
    );
    if (selected != null) {
      setState(() {
        _bookingId = int.tryParse(selected['id'].toString());
        if (selected['amount'] != null) {
          final amt = double.tryParse(selected['amount'].toString());
          if (amt != null) {
            _amountController.text = amt.toStringAsFixed(2);
          }
        }
        if (selected['barber_shop_id'] != null) {
          _barberShopId = int.tryParse(selected['barber_shop_id'].toString());
        }
      });
    }
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
    if (item['name'] != null && item['name'].toString().trim().isNotEmpty) {
      return item['name'].toString();
    }
    if (item['title'] != null && item['title'].toString().trim().isNotEmpty) {
      return item['title'].toString();
    }
    final id = item['id'];
    if (id != null && id.toString().isNotEmpty) {
      final custName = item['customer']?['name'] ?? item['customer_name'] ?? '';
      final servName = item['service']?['name'] ?? item['service_name'] ?? '';
      final price = item['total_price'] ?? item['amount'] ?? '';
      if (custName.isNotEmpty) {
        return "Takimi #$id - $custName ${servName.isNotEmpty ? '($servName)' : ''} ${price.toString().isNotEmpty ? '- $price Lekë' : ''}".trim();
      }
      return "Takimi #$id";
    }
    return paymentTr(context, 'form.select');
  }

  Future<void> _save(BuildContext context) async {
    if (!_formKey.currentState!.validate()) return;
    final payload = <String, dynamic>{};
    payload['barber_shop_id'] = _barberShopId;
    payload['booking_id'] = _bookingId;
    payload['amount'] = double.tryParse(_amountController.text);
    payload['method'] = _method;
    payload['status'] = _status;

    final files = <String, String>{};

    context.read<PaymentCubit>().save(payload, id: widget.item?['id']);
  }

  @override Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return BlocProvider(
      create: (_) => PaymentCubit(repository),
      child: BlocListener<PaymentCubit, PaymentState>(
        listener: (context, state) {
          if (state is PaymentSaved) Navigator.pop(context, true);
          if (state is PaymentFailure) {
            final message = state.message.trim().isEmpty ? paymentTr(context, 'form.save_error') : state.message;
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
            title: Text(widget.item == null ? paymentTr(context, 'form.create_title') : paymentTr(context, 'form.edit_title'), style: const TextStyle(fontWeight: FontWeight.w800)),
          ),
          body: _loading ? const Center(child: CircularProgressIndicator.adaptive()) : Form(key: _formKey, child: Builder(builder: (formContext) => ListView(padding: const EdgeInsets.fromLTRB(20, 12, 20, 120), children: [
            const SizedBox(height: 12),
            (AuthService.instance.user?['is_admin'] == true)
              ? _FieldShell(label: paymentTr(context, 'field.barber_shop_id'), child: InkWell(onTap: _pickbarberShopId, borderRadius: BorderRadius.circular(16), child: Container(padding: const EdgeInsets.all(16), decoration: BoxDecoration(border: Border.all(color: Theme.of(context).colorScheme.outlineVariant), borderRadius: BorderRadius.circular(16)), child: Row(children: [Expanded(child: Text(_displayName(_barberShopOptions.firstWhere((e) => e['id'].toString() == _barberShopId?.toString(), orElse: () => {'id': '', 'name': paymentTr(context, 'form.select')})))), const Icon(Icons.keyboard_arrow_down_rounded)]))))
              : _FieldShell(label: paymentTr(context, 'field.barber_shop_id'), child: Container(padding: const EdgeInsets.all(16), width: double.infinity, decoration: BoxDecoration(color: theme.colorScheme.surfaceContainerHighest.withOpacity(0.5), border: Border.all(color: theme.colorScheme.outlineVariant), borderRadius: BorderRadius.circular(16)), child: Text(AuthService.instance.user?['business']?['name'] ?? '', style: const TextStyle(fontWeight: FontWeight.bold)))),
            _FieldShell(label: paymentTr(context, 'field.booking_id'), child: InkWell(onTap: _pickbookingId, borderRadius: BorderRadius.circular(16), child: Container(padding: const EdgeInsets.all(16), decoration: BoxDecoration(border: Border.all(color: Theme.of(context).colorScheme.outlineVariant), borderRadius: BorderRadius.circular(16)), child: Row(children: [Expanded(child: Text(_displayName(_bookingOptions.firstWhere((e) => e['id'].toString() == _bookingId?.toString(), orElse: () => {'id': '', 'name': paymentTr(context, 'form.select')})))), const Icon(Icons.keyboard_arrow_down_rounded)])))),
            _FieldShell(label: paymentTr(context, 'field.amount'), child: TextFormField(controller: _amountController, keyboardType: const TextInputType.numberWithOptions(decimal: true), inputFormatters: [TextInputFormatter.withFunction((oldValue, newValue) { final text = newValue.text; if (text.isEmpty || RegExp(r'^\d*\.?\d{0,2}$').hasMatch(text)) return newValue; return oldValue; })],  decoration: InputDecoration(hintText: paymentTr(context, 'field.amount'), border: InputBorder.none, isDense: true), validator: (v) { if (v == null || v.trim().isEmpty) return paymentTr(context, 'form.required'); if (v != null && v.isNotEmpty && (double.tryParse(v) == null || !RegExp(r'^\d+(\.\d{1,2})?$').hasMatch(v))) return 'Enter a valid number with up to 2 decimals';  return null; })),
            _buildRowChips(
              label: paymentTr(context, 'field.method'),
              options: ['cash', 'card'],
              selectedValue: _method,
              onSelected: (v) => setState(() => _method = v),
            ),
            _buildRowChips(
              label: paymentTr(context, 'field.status'),
              options: ['pending', 'paid', 'refunded', 'failed'],
              selectedValue: _status,
              onSelected: (v) => setState(() => _status = v),
            ),

            const SizedBox(height: 14),
            BlocBuilder<PaymentCubit, PaymentState>(builder: (context, state) => PremiumButton(onPressed: () => _save(context), label: state is PaymentSaving ? paymentTr(context, 'form.saving') : paymentTr(context, 'form.save'), icon: Icons.check_rounded, loading: state is PaymentSaving, expand: true)),
          ]))),
        ),
      ),
    );
  }

  Widget _buildRowChips({
    required String label,
    required List<String> options,
    required String? selectedValue,
    required ValueChanged<String> onSelected,
  }) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;

    return _FieldShell(
      label: label,
      child: Padding(
        padding: const EdgeInsets.symmetric(vertical: 4),
        child: Row(
          children: options.map((opt) {
            final isSelected = selectedValue == opt;
            return Expanded(
              child: Padding(
                padding: EdgeInsets.symmetric(horizontal: options.indexOf(opt) > 0 ? 4 : 0),
                child: InkWell(
                  onTap: () => setState(() => onSelected(opt)),
                  borderRadius: BorderRadius.circular(12),
                  child: AnimatedContainer(
                    duration: const Duration(milliseconds: 200),
                    padding: const EdgeInsets.symmetric(vertical: 10),
                    alignment: Alignment.center,
                    decoration: BoxDecoration(
                      color: isSelected
                          ? theme.colorScheme.primary
                          : (isDark ? const Color(0xFF2D3139) : Colors.grey.shade200),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(
                        color: isSelected ? theme.colorScheme.primary : Colors.transparent,
                        width: 1.5,
                      ),
                    ),
                    child: Text(
                      opt.toUpperCase(),
                      style: TextStyle(
                        color: isSelected ? Colors.white : theme.colorScheme.onSurface,
                        fontWeight: FontWeight.bold,
                        fontSize: 11,
                      ),
                    ),
                  ),
                ),
              ),
            );
          }).toList(),
        ),
      ),
    );
  }

  @override void dispose() {
    _amountController.dispose();
    super.dispose();
  }
}
class _FormHeader extends StatelessWidget {
  final bool isEdit;
  const _FormHeader({required this.isEdit});
  @override Widget build(BuildContext context) => Container(padding: const EdgeInsets.all(20), decoration: BoxDecoration(gradient: LinearGradient(colors: [Theme.of(context).colorScheme.primaryContainer, Theme.of(context).colorScheme.secondaryContainer]), borderRadius: BorderRadius.circular(24)), child: Row(children: [Container(width: 48, height: 48, decoration: BoxDecoration(color: Theme.of(context).colorScheme.surface.withOpacity(.7), borderRadius: BorderRadius.circular(15)), child: Icon(isEdit ? Icons.edit_rounded : Icons.add_rounded)), const SizedBox(width: 14), Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(isEdit ? paymentTr(context, 'form.update_record') : paymentTr(context, 'form.new_record'), style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900)), const SizedBox(height: 3), Text(isEdit ? paymentTr(context, 'form.review_update') : paymentTr(context, 'form.fill_create'), style: TextStyle(fontSize: 12, color: Theme.of(context).colorScheme.onSurfaceVariant))]))]));
}

class _FieldShell extends StatelessWidget {
  final String label; final Widget child;
  const _FieldShell({required this.label, required this.child});
  @override Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;

    return Container(
      margin: const EdgeInsets.only(bottom: 14),
      padding: const EdgeInsets.fromLTRB(16, 10, 16, 6),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF1E212B) : theme.colorScheme.surfaceContainerHighest.withOpacity(.5),
        border: Border.all(
          color: isDark ? const Color(0xFF3B4052) : theme.colorScheme.outlineVariant,
          width: 1.2,
        ),
        borderRadius: BorderRadius.circular(18),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            label,
            style: TextStyle(
              fontSize: 11.5,
              fontWeight: FontWeight.w800,
              color: isDark ? theme.colorScheme.primary : theme.colorScheme.primary.withOpacity(0.85),
            ),
          ),
          const SizedBox(height: 2),
          child,
        ],
      ),
    );
  }
}

class _FilePickerCard extends StatelessWidget {
  final String? current, path; final VoidCallback onPick;
  const _FilePickerCard({this.current, this.path, required this.onPick});
  @override Widget build(BuildContext context) => InkWell(onTap: onPick, borderRadius: BorderRadius.circular(20), child: Container(height: 150, margin: const EdgeInsets.only(bottom: 14), decoration: BoxDecoration(borderRadius: BorderRadius.circular(20), border: Border.all(color: Theme.of(context).colorScheme.outlineVariant), color: Theme.of(context).colorScheme.surfaceContainerHighest.withOpacity(.25)), child: path != null ? ClipRRect(borderRadius: BorderRadius.circular(20), child: Image.file(File(path!), fit: BoxFit.cover, width: double.infinity)) : current != null ? ClipRRect(borderRadius: BorderRadius.circular(20), child: Image.network('${ApiService.serverUrl}/$current', fit: BoxFit.cover, width: double.infinity)) : const Column(mainAxisAlignment: MainAxisAlignment.center, children: [Icon(Icons.cloud_upload_outlined, size: 34), SizedBox(height: 8), Text('Tap to choose image', style: TextStyle(fontWeight: FontWeight.w700)), SizedBox(height: 3), Text('PNG, JPG', style: TextStyle(fontSize: 11, color: Colors.grey))])));
}
