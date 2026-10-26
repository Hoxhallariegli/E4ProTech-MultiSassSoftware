import 'dart:convert';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:mobile_gateway/core/widgets/premium_widgets.dart';
import 'package:mobile_gateway/core/branding/branding_cubit.dart';
import 'package:mobile_gateway/core/widgets/premium_image_picker.dart';
import 'package:mobile_gateway/l10n/subscription_renewal_localization.dart';
import '../cubit/subscription_renewal_cubit.dart';
import '../cubit/subscription_renewal_state.dart';
import '../../data/subscription_renewal_repository.dart';
import 'package:mobile_gateway/services/api_service.dart';
import 'package:mobile_gateway/services/auth_service.dart';

class SubscriptionRenewalFormPage extends StatefulWidget {
  final Map<String, dynamic>? item;
  const SubscriptionRenewalFormPage({super.key, this.item});
  @override State<SubscriptionRenewalFormPage> createState() => _SubscriptionRenewalFormPageState();
}

class _SubscriptionRenewalFormPageState extends State<SubscriptionRenewalFormPage> {
  final _formKey = GlobalKey<FormState>();
  final repository = SubscriptionRenewalRepository();
  bool _loading = true;
  String? _transferDocumentPath;

  String? _paymentMethod = 'cash';
  final _amountController = TextEditingController();
  final _notesController = TextEditingController();
  String? _status = 'pending';

  List<Map<String, dynamic>> _planOptions = [];
  int? _planId;

  @override void initState() { super.initState(); _init(); }

  Future<void> _init() async {
    _paymentMethod = widget.item?['payment_method']?.toString() ?? 'cash';
    if (_paymentMethod == 'bank_transfer') _paymentMethod = 'cash';

    final initialAmount = widget.item?['amount'];
    _amountController.text = initialAmount == null ? '' : (double.tryParse(initialAmount.toString())?.toStringAsFixed(2) ?? initialAmount.toString());
    _notesController.text = widget.item?['notes']?.toString() ?? '';
    _status = widget.item?['status']?.toString() ?? 'pending';

    try {
      final allPlans = await repository.lookup('plans');
      _planOptions = allPlans.where((p) {
        final name = (p['name'] ?? p['title'] ?? '').toString().toLowerCase();
        return !name.contains('trial') && !name.contains('prova');
      }).toList();
      if (widget.item?['plan_id'] != null) {
        _planId = int.tryParse(widget.item!['plan_id'].toString());
      }

      // Auto calculate price if empty
      if ((_amountController.text.isEmpty || _amountController.text == '0.00') && _planId != null) {
        final currentPlan = _planOptions.firstWhere(
          (p) => p['id'].toString() == _planId.toString(),
          orElse: () => <String, dynamic>{},
        );
        if (currentPlan.isNotEmpty) {
          final calculated = _calculateAmount(currentPlan, _notesController.text);
          _amountController.text = calculated.toStringAsFixed(2);
        }
      }
    } catch (_) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(subscription_renewalTr(context, 'form.could_not_load')), behavior: SnackBarBehavior.floating));
    } finally { if (mounted) setState(() => _loading = false); }
  }

  double _calculateAmount(Map<String, dynamic> plan, String notes) {
    final basePrice = double.tryParse(plan['price']?.toString() ?? '0') ?? 0.0;
    int months = 6;
    final lowerNotes = notes.toLowerCase();
    if (lowerNotes.contains('24') || lowerNotes.contains('2 vite') || lowerNotes.contains('2 years')) {
      months = 24;
    } else if (lowerNotes.contains('12') || lowerNotes.contains('1 vit') || lowerNotes.contains('1 year')) {
      months = 12;
    } else if (lowerNotes.contains('6')) {
      months = 6;
    }

    double discountPct = months == 12 ? 0.05 : (months == 24 ? 0.10 : 0.0);
    return (basePrice * months) * (1.0 - discountPct);
  }

  Future<void> _pickplanId() async {
    var filtered = List<Map<String, dynamic>>.from(_planOptions);
    final selected = await showModalBottomSheet<Map<String, dynamic>>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Theme.of(context).colorScheme.surface,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(30))),
      builder: (sheetContext) => StatefulBuilder(builder: (context, setSheet) => SizedBox(height: MediaQuery.of(context).size.height * .72, child: Padding(padding: const EdgeInsets.all(20), child: Column(children: [
        Container(width: 42, height: 4, decoration: BoxDecoration(color: Theme.of(context).dividerColor, borderRadius: BorderRadius.circular(10))),
        const SizedBox(height: 18), Align(alignment: Alignment.centerLeft, child: Text(subscription_renewalTr(sheetContext, 'field.plan_id'), style: const TextStyle(fontSize: 21, fontWeight: FontWeight.w800))),
        const SizedBox(height: 14), TextField(decoration: InputDecoration(prefixIcon: const Icon(Icons.search_rounded), hintText: subscription_renewalTr(sheetContext, 'form.search'), border: const OutlineInputBorder()), onChanged: (q) => setSheet(() => filtered = _planOptions.where((e) => _displayName(e).toLowerCase().contains(q.toLowerCase())).toList())),
        const SizedBox(height: 12), Expanded(child: ListView.separated(itemCount: filtered.length, separatorBuilder: (_, __) => const Divider(height: 1), itemBuilder: (_, i) { final option = filtered[i]; return ListTile(title: Text(_displayName(option), style: const TextStyle(fontWeight: FontWeight.w700)), subtitle: Text(option['price'] != null ? '${option['price']} €' : ''), trailing: option['id'].toString() == _planId?.toString() ? const Icon(Icons.check_circle_rounded) : null, onTap: () => Navigator.pop(sheetContext, option)); })),
      ])))),
    );
    if (selected != null) {
      setState(() {
        _planId = int.tryParse(selected['id'].toString());
        final price = _calculateAmount(selected, _notesController.text);
        _amountController.text = price.toStringAsFixed(2);
      });
    }
  }

  String _displayName(Map<String, dynamic> item) {
    return (item['name'] ?? item['title'] ?? 'ID: ' + item['id'].toString()).toString();
  }

  String _getPaymentMethodLabel(BuildContext context, String v) {
    switch (v) {
      case 'cash': return subscription_renewalTr(context, 'card.cash');
      case 'card': return subscription_renewalTr(context, 'card.card');
      case 'online': return subscription_renewalTr(context, 'card.online');
      default: return v;
    }
  }

  Future<void> _save(BuildContext context) async {
    if (!_formKey.currentState!.validate()) return;
    final payload = <String, dynamic>{};
    payload['plan_id'] = _planId;
    payload['payment_method'] = _paymentMethod;
    payload['amount'] = double.tryParse(_amountController.text);
    payload['notes'] = _notesController.text;
    payload['status'] = _status;

    final files = <String, String>{};
    if (_transferDocumentPath != null) files['transfer_document'] = _transferDocumentPath!;

    context.read<SubscriptionRenewalCubit>().save(payload, id: widget.item?['id'], files: files);
  }

  @override Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isAdmin = AuthService.instance.user?['is_admin'] == true;

    return BlocProvider(
      create: (_) => SubscriptionRenewalCubit(repository),
      child: BlocListener<SubscriptionRenewalCubit, SubscriptionRenewalState>(
        listener: (context, state) {
          if (state is SubscriptionRenewalSaved) Navigator.pop(context, true);
          if (state is SubscriptionRenewalFailure) {
            final message = state.message.trim().isEmpty ? subscription_renewalTr(context, 'form.save_error') : state.message;
            ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(message), behavior: SnackBarBehavior.floating, duration: const Duration(seconds: 4)));
          }
        },
        child: Scaffold(
          appBar: AppBar(
            leading: IconButton(
              tooltip: subscription_renewalTr(context, 'common.no'),
              icon: const Icon(Icons.arrow_back_rounded),
              onPressed: () => Navigator.of(context).maybePop(),
            ),
            title: Text(
              widget.item == null
                  ? subscription_renewalTr(context, 'form.create_title')
                  : subscription_renewalTr(context, 'form.edit_title'),
              style: const TextStyle(fontWeight: FontWeight.w800),
            ),
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

                        _FieldShell(
                          label: subscription_renewalTr(context, 'form.select_plan_label'),
                          child: InkWell(
                            onTap: _pickplanId,
                            borderRadius: BorderRadius.circular(16),
                            child: Container(
                              padding: const EdgeInsets.all(16),
                              decoration: BoxDecoration(border: Border.all(color: Theme.of(context).colorScheme.outlineVariant), borderRadius: BorderRadius.circular(16)),
                              child: Row(
                                children: [
                                  Expanded(
                                    child: Text(
                                      _displayName(_planOptions.firstWhere((e) => e['id'].toString() == _planId?.toString(), orElse: () => {'id': '', 'name': subscription_renewalTr(context, 'form.select_plan_label')})),
                                    ),
                                  ),
                                  const Icon(Icons.keyboard_arrow_down_rounded),
                                ],
                              ),
                            ),
                          ),
                        ),
                        _FieldShell(
                          label: subscription_renewalTr(context, 'form.select_payment_label'),
                          child: DropdownButtonFormField<String>(
                            value: ['cash', 'card', 'online'].contains(_paymentMethod) ? _paymentMethod : 'cash',
                            items: ['cash', 'card', 'online'].map((v) => DropdownMenuItem<String>(value: v, child: Text(_getPaymentMethodLabel(context, v)))).toList(),
                            onChanged: (v) => setState(() => _paymentMethod = v),
                            validator: (v) { if (v == null || v.isEmpty) return subscription_renewalTr(context, 'form.required'); return null; },
                            decoration: const InputDecoration(border: InputBorder.none, isDense: true),
                          ),
                        ),
                        PremiumImagePicker(
                          label: subscription_renewalTr(context, 'form.receipt_label'),
                          path: _transferDocumentPath,
                          currentUrl: widget.item?['transfer_document'] != null ? '${ApiService.serverUrl}/${widget.item!['transfer_document']}' : null,
                          onPicked: (p) => setState(() => _transferDocumentPath = p),
                        ),
                        _FieldShell(
                          label: subscription_renewalTr(context, 'form.amount_label'),
                          child: TextFormField(
                            controller: _amountController,
                            readOnly: !isAdmin,
                            enabled: isAdmin,
                            style: TextStyle(
                              fontWeight: FontWeight.bold,
                              fontSize: 16,
                              color: isAdmin ? theme.colorScheme.onSurface : theme.colorScheme.onSurface.withOpacity(0.7),
                            ),
                            keyboardType: const TextInputType.numberWithOptions(decimal: true),
                            inputFormatters: [
                              TextInputFormatter.withFunction((oldValue, newValue) {
                                final text = newValue.text;
                                if (text.isEmpty || RegExp(r'^\d*\.?\d{0,2}$').hasMatch(text)) return newValue;
                                return oldValue;
                              })
                            ],
                            decoration: InputDecoration(
                              hintText: subscription_renewalTr(context, 'form.amount_hint'),
                              border: InputBorder.none,
                              isDense: true,
                              suffixText: context.branding.currency,
                              suffixStyle: const TextStyle(fontWeight: FontWeight.bold),
                            ),
                            validator: (v) {
                              if (v == null || v.trim().isEmpty) return subscription_renewalTr(context, 'form.required');
                              if (double.tryParse(v) == null) return subscription_renewalTr(context, 'form.invalid_decimal');
                              return null;
                            },
                          ),
                        ),
                        _FieldShell(
                          label: subscription_renewalTr(context, 'form.notes_label'),
                          child: TextFormField(
                            controller: _notesController,
                            maxLines: 2,
                            decoration: InputDecoration(hintText: subscription_renewalTr(context, 'form.notes_hint'), border: InputBorder.none, isDense: true),
                          ),
                        ),
                        if (isAdmin)
                          _FieldShell(
                            label: subscription_renewalTr(context, 'form.status_label'),
                            child: DropdownButtonFormField<String>(
                              value: ['pending', 'approved', 'rejected'].contains(_status) ? _status : 'pending',
                              items: [
                                DropdownMenuItem(value: 'pending', child: Text(subscription_renewalTr(context, 'card.pending'))),
                                DropdownMenuItem(value: 'approved', child: Text(subscription_renewalTr(context, 'card.approved'))),
                                DropdownMenuItem(value: 'rejected', child: Text(subscription_renewalTr(context, 'card.rejected'))),
                              ],
                              onChanged: (v) => setState(() => _status = v),
                              decoration: const InputDecoration(border: InputBorder.none, isDense: true),
                            ),
                          ),

                        const SizedBox(height: 14),
                        BlocBuilder<SubscriptionRenewalCubit, SubscriptionRenewalState>(
                          builder: (context, state) => PremiumButton(
                            onPressed: () => _save(context),
                            label: state is SubscriptionRenewalSaving
                                ? subscription_renewalTr(context, 'form.saving')
                                : subscription_renewalTr(context, 'form.save'),
                            icon: Icons.check_circle_rounded,
                            loading: state is SubscriptionRenewalSaving,
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
    _amountController.dispose();
    _notesController.dispose();
    super.dispose();
  }
}

class _FormHeader extends StatelessWidget {
  final bool isEdit;
  const _FormHeader({required this.isEdit});
  @override Widget build(BuildContext context) => Container(padding: const EdgeInsets.all(20), decoration: BoxDecoration(gradient: LinearGradient(colors: [Theme.of(context).colorScheme.primaryContainer, Theme.of(context).colorScheme.secondaryContainer]), borderRadius: BorderRadius.circular(24)), child: Row(children: [Container(width: 48, height: 48, decoration: BoxDecoration(color: Theme.of(context).colorScheme.surface.withOpacity(.7), borderRadius: BorderRadius.circular(15)), child: Icon(isEdit ? Icons.edit_rounded : Icons.add_rounded)), const SizedBox(width: 14), Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(isEdit ? subscription_renewalTr(context, 'form.edit_title') : subscription_renewalTr(context, 'form.create_title'), style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900)), const SizedBox(height: 3), Text(isEdit ? subscription_renewalTr(context, 'form.review_update') : subscription_renewalTr(context, 'form.fill_create'), style: TextStyle(fontSize: 12, color: Theme.of(context).colorScheme.onSurfaceVariant))]))]));
}

class _FieldShell extends StatelessWidget {
  final String label; final Widget child;
  const _FieldShell({required this.label, required this.child});
  @override Widget build(BuildContext context) => Container(margin: const EdgeInsets.only(bottom: 14), padding: const EdgeInsets.fromLTRB(16, 12, 16, 6), decoration: BoxDecoration(color: Theme.of(context).colorScheme.surfaceContainerHighest.withOpacity(.35), border: Border.all(color: Theme.of(context).colorScheme.outlineVariant.withOpacity(.7)), borderRadius: BorderRadius.circular(18)), child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(label, style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: Theme.of(context).colorScheme.onSurfaceVariant)), child]));
}
