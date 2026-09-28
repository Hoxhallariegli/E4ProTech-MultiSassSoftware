import 'dart:convert';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:image_picker/image_picker.dart';
import 'package:mobile_gateway/core/widgets/premium_widgets.dart';
import 'package:mobile_gateway/core/widgets/premium_image_picker.dart';
import 'package:mobile_gateway/l10n/customer_localization.dart';
import '../cubit/customer_cubit.dart';
import '../cubit/customer_state.dart';
import '../../data/customer_repository.dart';
import 'package:mobile_gateway/services/api_service.dart';
import 'package:mobile_gateway/services/auth_service.dart';

class CustomerFormPage extends StatefulWidget {
  final Map<String, dynamic>? item;
  const CustomerFormPage({super.key, this.item});
  @override State<CustomerFormPage> createState() => _CustomerFormPageState();
}

class _CustomerFormPageState extends State<CustomerFormPage> {
  final _formKey = GlobalKey<FormState>();
  final repository = CustomerRepository();
  bool _loading = true;
  String? _photoPath;

  final _nameController = TextEditingController();
  final _phoneController = TextEditingController();
  final _emailController = TextEditingController();

  bool _isBlocked = false;
  int _totalBookings = 0;
  int _noShowCount = 0;

  List<Map<String, dynamic>> _barberShopOptions = [];
  int? _barberShopId;

  @override void initState() { super.initState(); _init(); }

  Future<void> _init() async {
    _nameController.text = widget.item?['name']?.toString() ?? '';
    _phoneController.text = widget.item?['phone']?.toString() ?? '';
    _emailController.text = widget.item?['email']?.toString() ?? '';
    _totalBookings = int.tryParse(widget.item?['total_bookings']?.toString() ?? '') ?? 0;
    _noShowCount = int.tryParse(widget.item?['no_show_count']?.toString() ?? '') ?? 0;
    final blockedVal = widget.item?['blocked_at'];
    _isBlocked = blockedVal != null && blockedVal.toString().trim().isNotEmpty;

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
        const SizedBox(height: 18), Align(alignment: Alignment.centerLeft, child: Text(customerTr(sheetContext, 'field.barber_shop_id'), style: const TextStyle(fontSize: 21, fontWeight: FontWeight.w800))),
        const SizedBox(height: 14), TextField(decoration: InputDecoration(prefixIcon: const Icon(Icons.search_rounded), hintText: customerTr(sheetContext, 'form.search'), border: const OutlineInputBorder()), onChanged: (q) => setSheet(() => filtered = _barberShopOptions.where((e) => _displayName(e).toLowerCase().contains(q.toLowerCase())).toList())),
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
    payload['phone'] = _phoneController.text.trim();
    payload['email'] = _emailController.text.trim();
    payload['total_bookings'] = _totalBookings;
    payload['no_show_count'] = _noShowCount;
    payload['blocked_at'] = _isBlocked ? (widget.item?['blocked_at'] ?? DateTime.now().toIso8601String()) : null;

    final files = <String, String>{};
    if (_photoPath != null) files['photo'] = _photoPath!;

    context.read<CustomerCubit>().save(payload, id: widget.item?['id'], files: files);
  }

  @override Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isEdit = widget.item != null;

    return BlocProvider(
      create: (_) => CustomerCubit(repository),
      child: BlocListener<CustomerCubit, CustomerState>(
        listener: (context, state) {
          if (state is CustomerSaved) Navigator.pop(context, true);
          if (state is CustomerFailure) {
            final message = state.message.trim().isEmpty ? customerTr(context, 'form.save_error') : state.message;
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
            title: Text(isEdit ? customerTr(context, 'form.edit_title') : customerTr(context, 'form.create_title'), style: const TextStyle(fontWeight: FontWeight.w800)),
          ),
          body: _loading ? const Center(child: CircularProgressIndicator.adaptive()) : Form(key: _formKey, child: Builder(builder: (formContext) => ListView(padding: const EdgeInsets.fromLTRB(20, 12, 20, 120), children: [
            const SizedBox(height: 12),
            (AuthService.instance.user?['is_admin'] == true)
              ? _FieldShell(label: customerTr(context, 'field.barber_shop_id'), child: InkWell(onTap: _pickbarberShopId, borderRadius: BorderRadius.circular(16), child: Container(padding: const EdgeInsets.all(16), decoration: BoxDecoration(border: Border.all(color: Theme.of(context).colorScheme.outlineVariant), borderRadius: BorderRadius.circular(16)), child: Row(children: [Expanded(child: Text(_displayName(_barberShopOptions.firstWhere((e) => e['id'].toString() == _barberShopId?.toString(), orElse: () => {'id': '', 'name': customerTr(context, 'form.select')})))), const Icon(Icons.keyboard_arrow_down_rounded)]))))
              : _FieldShell(label: customerTr(context, 'field.barber_shop_id'), child: Container(padding: const EdgeInsets.all(16), width: double.infinity, decoration: BoxDecoration(color: theme.colorScheme.surfaceContainerHighest.withOpacity(0.5), border: Border.all(color: theme.colorScheme.outlineVariant), borderRadius: BorderRadius.circular(16)), child: Text(AuthService.instance.user?['business']?['name'] ?? '', style: const TextStyle(fontWeight: FontWeight.bold)))),
            _FieldShell(label: customerTr(context, 'field.name'), child: TextFormField(controller: _nameController, decoration: InputDecoration(hintText: customerTr(context, 'field.name'), border: InputBorder.none, isDense: true), validator: (v) { if (v == null || v.trim().isEmpty) return customerTr(context, 'form.required'); return null; })),
            _FieldShell(label: customerTr(context, 'field.phone'), child: TextFormField(controller: _phoneController, keyboardType: TextInputType.phone, decoration: InputDecoration(hintText: customerTr(context, 'field.phone'), border: InputBorder.none, isDense: true), validator: (v) { if (v == null || v.trim().isEmpty) return customerTr(context, 'form.required'); return null; })),
            _FieldShell(label: customerTr(context, 'field.email'), child: TextFormField(controller: _emailController, keyboardType: TextInputType.emailAddress, decoration: InputDecoration(hintText: customerTr(context, 'field.email'), border: InputBorder.none, isDense: true), validator: (v) { return null; })),
            PremiumImagePicker(label: customerTr(context, 'field.photo'), path: _photoPath, currentUrl: widget.item?['photo'] != null ? '${ApiService.serverUrl}/${widget.item!['photo']}' : null, onPicked: (p) => setState(() => _photoPath = p)),

            if (isEdit) ...[
              const SizedBox(height: 8),
              Row(
                children: [
                  Expanded(
                    child: Container(
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: theme.colorScheme.surfaceContainerHighest.withOpacity(0.3),
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: theme.colorScheme.outlineVariant.withOpacity(0.5)),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text('Totali i Prenotimeve', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.grey)),
                          const SizedBox(height: 4),
                          Text('$_totalBookings takime', style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w900)),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Container(
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: theme.colorScheme.surfaceContainerHighest.withOpacity(0.3),
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: theme.colorScheme.outlineVariant.withOpacity(0.5)),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text('Mosardhje (No-Show)', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.grey)),
                          const SizedBox(height: 4),
                          Text('$_noShowCount herë', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w900, color: _noShowCount > 0 ? Colors.red : null)),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 14),
            ],

            Container(
              decoration: BoxDecoration(
                border: Border.all(color: theme.colorScheme.outlineVariant),
                borderRadius: BorderRadius.circular(16),
              ),
              child: SwitchListTile.adaptive(
                contentPadding: const EdgeInsets.symmetric(horizontal: 16),
                title: const Text('Blloko Klientin', style: TextStyle(fontWeight: FontWeight.w700)),
                subtitle: const Text('Klientët e bllokuar nuk lejohen të bëjnë rezervime online.', style: TextStyle(fontSize: 11, color: Colors.grey)),
                value: _isBlocked,
                activeColor: Colors.red,
                onChanged: (v) => setState(() => _isBlocked = v),
              ),
            ),

            const SizedBox(height: 20),
            BlocBuilder<CustomerCubit, CustomerState>(builder: (context, state) => PremiumButton(onPressed: () => _save(context), label: state is CustomerSaving ? customerTr(context, 'form.saving') : customerTr(context, 'form.save'), icon: Icons.check_rounded, loading: state is CustomerSaving, expand: true)),
          ]))),
        ),
      ),
    );
  }

  @override void dispose() {
    _nameController.dispose();
    _phoneController.dispose();
    _emailController.dispose();
    super.dispose();
  }
}

class _FormHeader extends StatelessWidget {
  final bool isEdit;
  const _FormHeader({required this.isEdit});
  @override Widget build(BuildContext context) => Container(padding: const EdgeInsets.all(20), decoration: BoxDecoration(gradient: LinearGradient(colors: [Theme.of(context).colorScheme.primaryContainer, Theme.of(context).colorScheme.secondaryContainer]), borderRadius: BorderRadius.circular(24)), child: Row(children: [Container(width: 48, height: 48, decoration: BoxDecoration(color: Theme.of(context).colorScheme.surface.withOpacity(.7), borderRadius: BorderRadius.circular(15)), child: Icon(isEdit ? Icons.edit_rounded : Icons.person_add_rounded)), const SizedBox(width: 14), Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(isEdit ? customerTr(context, 'form.update_record') : customerTr(context, 'form.new_record'), style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900)), const SizedBox(height: 3), Text(isEdit ? customerTr(context, 'form.review_update') : customerTr(context, 'form.fill_create'), style: TextStyle(fontSize: 12, color: Theme.of(context).colorScheme.onSurfaceVariant))]))]));
}

class _FieldShell extends StatelessWidget {
  final String label; final Widget child;
  const _FieldShell({required this.label, required this.child});
  @override Widget build(BuildContext context) => Container(margin: const EdgeInsets.only(bottom: 14), padding: const EdgeInsets.fromLTRB(16, 12, 16, 6), decoration: BoxDecoration(color: Theme.of(context).colorScheme.surfaceContainerHighest.withOpacity(.35), border: Border.all(color: Theme.of(context).colorScheme.outlineVariant.withOpacity(.7)), borderRadius: BorderRadius.circular(18)), child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(label, style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: Theme.of(context).colorScheme.onSurfaceVariant)), child]));
}
