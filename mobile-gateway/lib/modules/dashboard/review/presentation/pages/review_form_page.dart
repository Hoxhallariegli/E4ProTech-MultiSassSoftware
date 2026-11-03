import 'dart:convert';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:image_picker/image_picker.dart';
import 'package:mobile_gateway/core/widgets/premium_widgets.dart';
import 'package:mobile_gateway/core/widgets/premium_image_picker.dart';
import 'package:mobile_gateway/l10n/review_localization.dart';
import '../cubit/review_cubit.dart';
import '../cubit/review_state.dart';
import '../../data/review_repository.dart';
import 'package:mobile_gateway/services/api_service.dart';
import 'package:mobile_gateway/services/auth_service.dart';

class ReviewFormPage extends StatefulWidget {
  final Map<String, dynamic>? item;
  const ReviewFormPage({super.key, this.item});
  @override State<ReviewFormPage> createState() => _ReviewFormPageState();
}

class _ReviewFormPageState extends State<ReviewFormPage> {
  final _formKey = GlobalKey<FormState>();
  final repository = ReviewRepository();
  bool _loading = true;

  final _ratingController = TextEditingController();
  final _commentController = TextEditingController();

  List<Map<String, dynamic>> _barberShopOptions = [];
  int? _barberShopId;
  List<Map<String, dynamic>> _barberOptions = [];
  int? _barberId;
  List<Map<String, dynamic>> _customerOptions = [];
  int? _customerId;
  List<Map<String, dynamic>> _bookingOptions = [];
  int? _bookingId;


  @override void initState() {
    super.initState();
    _ratingController.text = widget.item?['rating']?.toString() ?? '5'; // Default 5 stars
    _init();
  }

  Future<void> _init() async {
    _ratingController.text = widget.item?['rating']?.toString() ?? '5';
    _commentController.text = widget.item?['comment']?.toString() ?? '';

    try {
      _barberShopOptions = await repository.lookup('barber-shops');
      if (widget.item?['barber_shop_id'] != null) _barberShopId = int.tryParse(widget.item!['barber_shop_id'].toString());
      if (_barberShopId == null && AuthService.instance.user?['is_admin'] != true) _barberShopId = AuthService.instance.user?['barber_shop_id'] as int?;
      _barberOptions = await repository.lookup('barbers');
      if (widget.item?['barber_id'] != null) _barberId = int.tryParse(widget.item!['barber_id'].toString());
      _customerOptions = await repository.lookup('customers');
      if (widget.item?['customer_id'] != null) _customerId = int.tryParse(widget.item!['customer_id'].toString());
      _bookingOptions = await repository.lookup('bookings');
      if (widget.item?['booking_id'] != null) _bookingId = int.tryParse(widget.item!['booking_id'].toString());
    } catch (_) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(reviewTr(context, 'form.could_not_load')), behavior: SnackBarBehavior.floating));
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
        const SizedBox(height: 18), Align(alignment: Alignment.centerLeft, child: Text(reviewTr(sheetContext, 'field.barber_shop_id'), style: const TextStyle(fontSize: 21, fontWeight: FontWeight.w800))),
        const SizedBox(height: 14), TextField(decoration: InputDecoration(prefixIcon: const Icon(Icons.search_rounded), hintText: reviewTr(sheetContext, 'form.search'), border: const OutlineInputBorder()), onChanged: (q) => setSheet(() => filtered = _barberShopOptions.where((e) => _displayName(e).toLowerCase().contains(q.toLowerCase())).toList())),
        const SizedBox(height: 12), Expanded(child: ListView.separated(itemCount: filtered.length, separatorBuilder: (_, __) => const Divider(height: 1), itemBuilder: (_, i) { final option = filtered[i]; return ListTile(title: Text(_displayName(option), style: const TextStyle(fontWeight: FontWeight.w700)), trailing: option['id'].toString() == _barberShopId?.toString() ? const Icon(Icons.check_circle_rounded) : null, onTap: () => Navigator.pop(sheetContext, option)); })),
      ])))),
    );
    if (selected != null) setState(() => _barberShopId = int.tryParse(selected['id'].toString()));
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
        const SizedBox(height: 18), Align(alignment: Alignment.centerLeft, child: Text(reviewTr(sheetContext, 'field.barber_id'), style: const TextStyle(fontSize: 21, fontWeight: FontWeight.w800))),
        const SizedBox(height: 14), TextField(decoration: InputDecoration(prefixIcon: const Icon(Icons.search_rounded), hintText: reviewTr(sheetContext, 'form.search'), border: const OutlineInputBorder()), onChanged: (q) => setSheet(() => filtered = _barberOptions.where((e) => _displayName(e).toLowerCase().contains(q.toLowerCase())).toList())),
        const SizedBox(height: 12), Expanded(child: ListView.separated(itemCount: filtered.length, separatorBuilder: (_, __) => const Divider(height: 1), itemBuilder: (_, i) { final option = filtered[i]; return ListTile(title: Text(_displayName(option), style: const TextStyle(fontWeight: FontWeight.w700)), trailing: option['id'].toString() == _barberId?.toString() ? const Icon(Icons.check_circle_rounded) : null, onTap: () => Navigator.pop(sheetContext, option)); })),
      ])))),
    );

    if (selected != null) {
      setState(() {
        _barberId = int.tryParse(selected['id'].toString());
        if (selected['barber_shop_id'] != null) {
          _barberShopId = int.tryParse(selected['barber_shop_id'].toString());
        }

        // Reset Customer & Booking if they don't belong to selected barber
        if (_customerId != null) {
          final validCustomerIds = _bookingOptions
              .where((b) => b['barber_id']?.toString() == _barberId.toString())
              .map((b) => b['customer_id']?.toString())
              .whereType<String>()
              .toSet();
          if (!validCustomerIds.contains(_customerId.toString())) {
            _customerId = null;
            _bookingId = null;
          }
        }
      });
    }
  }

  Future<void> _pickcustomerId() async {
    var filtered = List<Map<String, dynamic>>.from(_customerOptions);

    // Smart Cascade Filter: If a Barber is selected, show only customers with bookings for that barber
    if (_barberId != null) {
      final barberBookingCustomerIds = _bookingOptions
          .where((b) => b['barber_id']?.toString() == _barberId.toString())
          .map((b) => b['customer_id']?.toString())
          .whereType<String>()
          .toSet();

      if (barberBookingCustomerIds.isNotEmpty) {
        filtered = filtered.where((c) => barberBookingCustomerIds.contains(c['id']?.toString())).toList();
      }
    }

    final selected = await showModalBottomSheet<Map<String, dynamic>>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Theme.of(context).colorScheme.surface,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(30))),
      builder: (sheetContext) => StatefulBuilder(builder: (context, setSheet) => SizedBox(height: MediaQuery.of(context).size.height * .72, child: Padding(padding: const EdgeInsets.all(20), child: Column(children: [
        Container(width: 42, height: 4, decoration: BoxDecoration(color: Theme.of(context).dividerColor, borderRadius: BorderRadius.circular(10))),
        const SizedBox(height: 18), Align(alignment: Alignment.centerLeft, child: Text(reviewTr(sheetContext, 'field.customer_id'), style: const TextStyle(fontSize: 21, fontWeight: FontWeight.w800))),
        const SizedBox(height: 14), TextField(decoration: InputDecoration(prefixIcon: const Icon(Icons.search_rounded), hintText: reviewTr(sheetContext, 'form.search'), border: const OutlineInputBorder()), onChanged: (q) => setSheet(() => filtered = _customerOptions.where((e) => _displayName(e).toLowerCase().contains(q.toLowerCase())).toList())),
        const SizedBox(height: 12), Expanded(child: ListView.separated(itemCount: filtered.length, separatorBuilder: (_, __) => const Divider(height: 1), itemBuilder: (_, i) { final option = filtered[i]; return ListTile(title: Text(_displayName(option), style: const TextStyle(fontWeight: FontWeight.w700)), trailing: option['id'].toString() == _customerId?.toString() ? const Icon(Icons.check_circle_rounded) : null, onTap: () => Navigator.pop(sheetContext, option)); })),
      ])))),
    );

    if (selected != null) {
      setState(() {
        _customerId = int.tryParse(selected['id'].toString());
        if (selected['barber_shop_id'] != null) {
          _barberShopId = int.tryParse(selected['barber_shop_id'].toString());
        }

        // Reset Booking if it doesn't match selected customer
        if (_bookingId != null) {
          final currentBooking = _bookingOptions.firstWhere(
            (b) => b['id']?.toString() == _bookingId.toString(),
            orElse: () => {},
          );
          if (currentBooking['customer_id']?.toString() != _customerId.toString()) {
            _bookingId = null;
          }
        }
      });
    }
  }

  Future<void> _pickbookingId() async {
    // Smart Filter: Filter booking options if a barber or customer is already selected
    var filtered = List<Map<String, dynamic>>.from(_bookingOptions);
    if (_barberId != null) {
      filtered = filtered.where((b) => b['barber_id']?.toString() == _barberId.toString()).toList();
    }
    if (_customerId != null) {
      filtered = filtered.where((b) => b['customer_id']?.toString() == _customerId.toString()).toList();
    }

    final selected = await showModalBottomSheet<Map<String, dynamic>>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Theme.of(context).colorScheme.surface,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(30))),
      builder: (sheetContext) => StatefulBuilder(builder: (context, setSheet) => SizedBox(height: MediaQuery.of(context).size.height * .72, child: Padding(padding: const EdgeInsets.all(20), child: Column(children: [
        Container(width: 42, height: 4, decoration: BoxDecoration(color: Theme.of(context).dividerColor, borderRadius: BorderRadius.circular(10))),
        const SizedBox(height: 18), Align(alignment: Alignment.centerLeft, child: Text(reviewTr(sheetContext, 'field.booking_id'), style: const TextStyle(fontSize: 21, fontWeight: FontWeight.w800))),
        const SizedBox(height: 14), TextField(decoration: InputDecoration(prefixIcon: const Icon(Icons.search_rounded), hintText: reviewTr(sheetContext, 'form.search'), border: const OutlineInputBorder()), onChanged: (q) => setSheet(() => filtered = _bookingOptions.where((e) => _displayName(e).toLowerCase().contains(q.toLowerCase())).toList())),
        const SizedBox(height: 12), Expanded(child: ListView.separated(itemCount: filtered.length, separatorBuilder: (_, __) => const Divider(height: 1), itemBuilder: (_, i) { final option = filtered[i]; return ListTile(title: Text(_displayName(option), style: const TextStyle(fontWeight: FontWeight.w700)), trailing: option['id'].toString() == _bookingId?.toString() ? const Icon(Icons.check_circle_rounded) : null, onTap: () => Navigator.pop(sheetContext, option)); })),
      ])))),
    );

    if (selected != null) {
      setState(() {
        _bookingId = int.tryParse(selected['id'].toString());

        // Smart Relationship Auto-Fill: Auto-select Staff Member, Customer, and Shop from selected Booking!
        if (selected['barber_id'] != null) {
          _barberId = int.tryParse(selected['barber_id'].toString());
        }
        if (selected['customer_id'] != null) {
          _customerId = int.tryParse(selected['customer_id'].toString());
        }
        if (selected['barber_shop_id'] != null) {
          _barberShopId = int.tryParse(selected['barber_shop_id'].toString());
        }
      });
    }
  }

  Widget _buildStarRatingPicker() {
    int currentRating = int.tryParse(_ratingController.text) ?? 5;
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;

    return _FieldShell(
      label: 'VLERËSIMI ME YJE (RATING)',
      child: Padding(
        padding: const EdgeInsets.symmetric(vertical: 8),
        child: Column(
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              children: List.generate(5, (index) {
                final starNumber = index + 1;
                final isFilled = starNumber <= currentRating;

                return InkWell(
                  onTap: () {
                    setState(() {
                      _ratingController.text = starNumber.toString();
                    });
                  },
                  borderRadius: BorderRadius.circular(20),
                  child: AnimatedContainer(
                    duration: const Duration(milliseconds: 150),
                    padding: const EdgeInsets.all(6),
                    child: Icon(
                      isFilled ? Icons.star_rounded : Icons.star_outline_rounded,
                      size: 38,
                      color: isFilled ? Colors.amber.shade600 : (isDark ? Colors.grey.shade600 : Colors.grey.shade400),
                    ),
                  ),
                );
              }),
            ),
            const SizedBox(height: 4),
            Text(
              '$currentRating / 5 Yje',
              style: TextStyle(
                fontWeight: FontWeight.bold,
                fontSize: 13,
                color: Colors.amber.shade700,
              ),
            ),
          ],
        ),
      ),
    );
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
      if (custName.isNotEmpty) {
        return "Takimi #$id - $custName ${servName.isNotEmpty ? '($servName)' : ''}".trim();
      }
      return "Takimi #$id";
    }
    return reviewTr(context, 'form.select');
  }

  Future<void> _save(BuildContext context) async {
    if (!_formKey.currentState!.validate()) return;
    final payload = <String, dynamic>{};
    payload['barber_shop_id'] = _barberShopId;
    payload['barber_id'] = _barberId;
    payload['customer_id'] = _customerId;
    payload['booking_id'] = _bookingId;
    payload['rating'] = int.tryParse(_ratingController.text) ?? 5;
    payload['comment'] = _commentController.text;

    context.read<ReviewCubit>().save(payload, id: widget.item?['id']);
  }

  @override Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return BlocProvider(
      create: (_) => ReviewCubit(repository),
      child: BlocListener<ReviewCubit, ReviewState>(
        listener: (context, state) {
          if (state is ReviewSaved) Navigator.pop(context, true);
          if (state is ReviewFailure) {
            final message = state.message.trim().isEmpty ? reviewTr(context, 'form.save_error') : state.message;
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
            title: Text(widget.item == null ? reviewTr(context, 'form.create_title') : reviewTr(context, 'form.edit_title'), style: const TextStyle(fontWeight: FontWeight.w800)),
          ),
          body: _loading ? const Center(child: CircularProgressIndicator.adaptive()) : Form(key: _formKey, child: Builder(builder: (formContext) => ListView(padding: const EdgeInsets.fromLTRB(20, 12, 20, 120), children: [
            _FormHeader(isEdit: widget.item != null),
            const SizedBox(height: 22),
            (AuthService.instance.user?['is_admin'] == true)
              ? _FieldShell(label: reviewTr(context, 'field.barber_shop_id'), child: InkWell(onTap: _pickbarberShopId, borderRadius: BorderRadius.circular(16), child: Container(padding: const EdgeInsets.all(16), decoration: BoxDecoration(border: Border.all(color: Theme.of(context).colorScheme.outlineVariant), borderRadius: BorderRadius.circular(16)), child: Row(children: [Expanded(child: Text(_displayName(_barberShopOptions.firstWhere((e) => e['id'].toString() == _barberShopId?.toString(), orElse: () => {'id': '', 'name': reviewTr(context, 'form.select')})))), const Icon(Icons.keyboard_arrow_down_rounded)]))))
              : _FieldShell(label: reviewTr(context, 'field.barber_shop_id'), child: Container(padding: const EdgeInsets.all(16), width: double.infinity, decoration: BoxDecoration(color: theme.colorScheme.surfaceContainerHighest.withOpacity(0.5), border: Border.all(color: theme.colorScheme.outlineVariant), borderRadius: BorderRadius.circular(16)), child: Text(AuthService.instance.user?['business']?['name'] ?? '', style: const TextStyle(fontWeight: FontWeight.bold)))),
            _FieldShell(label: reviewTr(context, 'field.barber_id'), child: InkWell(onTap: _pickbarberId, borderRadius: BorderRadius.circular(16), child: Container(padding: const EdgeInsets.all(16), decoration: BoxDecoration(border: Border.all(color: Theme.of(context).colorScheme.outlineVariant), borderRadius: BorderRadius.circular(16)), child: Row(children: [Expanded(child: Text(_displayName(_barberOptions.firstWhere((e) => e['id'].toString() == _barberId?.toString(), orElse: () => {'id': '', 'name': reviewTr(context, 'form.select')})))), const Icon(Icons.keyboard_arrow_down_rounded)])))),
            _FieldShell(label: reviewTr(context, 'field.customer_id'), child: InkWell(onTap: _pickcustomerId, borderRadius: BorderRadius.circular(16), child: Container(padding: const EdgeInsets.all(16), decoration: BoxDecoration(border: Border.all(color: Theme.of(context).colorScheme.outlineVariant), borderRadius: BorderRadius.circular(16)), child: Row(children: [Expanded(child: Text(_displayName(_customerOptions.firstWhere((e) => e['id'].toString() == _customerId?.toString(), orElse: () => {'id': '', 'name': reviewTr(context, 'form.select')})))), const Icon(Icons.keyboard_arrow_down_rounded)])))),
            _FieldShell(label: reviewTr(context, 'field.booking_id'), child: InkWell(onTap: _pickbookingId, borderRadius: BorderRadius.circular(16), child: Container(padding: const EdgeInsets.all(16), decoration: BoxDecoration(border: Border.all(color: Theme.of(context).colorScheme.outlineVariant), borderRadius: BorderRadius.circular(16)), child: Row(children: [Expanded(child: Text(_displayName(_bookingOptions.firstWhere((e) => e['id'].toString() == _bookingId?.toString(), orElse: () => {'id': '', 'name': reviewTr(context, 'form.select')})))), const Icon(Icons.keyboard_arrow_down_rounded)])))),
            _buildStarRatingPicker(),
            _FieldShell(label: reviewTr(context, 'field.comment'), child: TextFormField(controller: _commentController, decoration: InputDecoration(hintText: reviewTr(context, 'field.comment'), border: InputBorder.none, isDense: true), validator: (v) {  return null; })),

            const SizedBox(height: 14),
            BlocBuilder<ReviewCubit, ReviewState>(builder: (context, state) => PremiumButton(onPressed: () => _save(context), label: state is ReviewSaving ? reviewTr(context, 'form.saving') : reviewTr(context, 'form.save'), icon: Icons.check_rounded, loading: state is ReviewSaving, expand: true)),
          ]))),
        ),
      ),
    );
  }

  @override void dispose() {
    _ratingController.dispose();
    _commentController.dispose();
    super.dispose();
  }
}
class _FormHeader extends StatelessWidget {
  final bool isEdit;
  const _FormHeader({required this.isEdit});
  @override Widget build(BuildContext context) => Container(padding: const EdgeInsets.all(20), decoration: BoxDecoration(gradient: LinearGradient(colors: [Theme.of(context).colorScheme.primaryContainer, Theme.of(context).colorScheme.secondaryContainer]), borderRadius: BorderRadius.circular(24)), child: Row(children: [Container(width: 48, height: 48, decoration: BoxDecoration(color: Theme.of(context).colorScheme.surface.withOpacity(.7), borderRadius: BorderRadius.circular(15)), child: Icon(isEdit ? Icons.edit_rounded : Icons.add_rounded)), const SizedBox(width: 14), Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(isEdit ? reviewTr(context, 'form.update_record') : reviewTr(context, 'form.new_record'), style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900)), const SizedBox(height: 3), Text(isEdit ? reviewTr(context, 'form.review_update') : reviewTr(context, 'form.fill_create'), style: TextStyle(fontSize: 12, color: Theme.of(context).colorScheme.onSurfaceVariant))]))]));
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
