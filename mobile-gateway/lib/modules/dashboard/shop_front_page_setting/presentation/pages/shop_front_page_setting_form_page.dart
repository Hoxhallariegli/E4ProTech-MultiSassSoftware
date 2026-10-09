import 'dart:convert';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:image_picker/image_picker.dart';
import 'package:mobile_gateway/core/widgets/premium_widgets.dart';
import 'package:mobile_gateway/core/widgets/premium_image_picker.dart';
import 'package:mobile_gateway/l10n/shop_front_page_setting_localization.dart';
import '../cubit/shop_front_page_setting_cubit.dart';
import '../cubit/shop_front_page_setting_state.dart';
import '../../data/shop_front_page_setting_repository.dart';
import 'package:mobile_gateway/services/api_service.dart';
import 'package:mobile_gateway/services/auth_service.dart';

class ShopFrontPageSettingFormPage extends StatefulWidget {
  final Map<String, dynamic>? item;
  const ShopFrontPageSettingFormPage({super.key, this.item});
  @override State<ShopFrontPageSettingFormPage> createState() => _ShopFrontPageSettingFormPageState();
}

class _ShopFrontPageSettingFormPageState extends State<ShopFrontPageSettingFormPage> {
  final _formKey = GlobalKey<FormState>();
  final repository = ShopFrontPageSettingRepository();
  bool _loading = true;

  final _heroTitleController = TextEditingController();
  final _heroSubtitleController = TextEditingController();
  final _heroButtonTextController = TextEditingController();
  final _servicesBadgeTextController = TextEditingController();
  final _servicesTitleController = TextEditingController();
  final _staffBadgeTextController = TextEditingController();
  final _staffTitleController = TextEditingController();
  final _contactPhoneController = TextEditingController();
  final _contactEmailController = TextEditingController();
  final _contactAddressController = TextEditingController();
  final _googleMapsUrlController = TextEditingController();
  final _footerTextController = TextEditingController();

  List<Map<String, dynamic>> _barberShopOptions = [];
  int? _barberShopId;


  @override void initState() { super.initState(); _init(); }

  Future<void> _init() async {
    _heroTitleController.text = widget.item?['hero_title']?.toString() ?? '';
    _heroSubtitleController.text = widget.item?['hero_subtitle']?.toString() ?? '';
    _heroButtonTextController.text = widget.item?['hero_button_text']?.toString() ?? '';
    _servicesBadgeTextController.text = widget.item?['services_badge_text']?.toString() ?? '';
    _servicesTitleController.text = widget.item?['services_title']?.toString() ?? '';
    _staffBadgeTextController.text = widget.item?['staff_badge_text']?.toString() ?? '';
    _staffTitleController.text = widget.item?['staff_title']?.toString() ?? '';
    _contactPhoneController.text = widget.item?['contact_phone']?.toString() ?? '';
    _contactEmailController.text = widget.item?['contact_email']?.toString() ?? '';
    _contactAddressController.text = widget.item?['contact_address']?.toString() ?? '';
    _googleMapsUrlController.text = widget.item?['google_maps_url']?.toString() ?? '';
    _footerTextController.text = widget.item?['footer_text']?.toString() ?? '';

    try {
    _barberShopOptions = await repository.lookup('barber-shops');
    if (widget.item?['barber_shop_id'] != null) _barberShopId = int.tryParse(widget.item!['barber_shop_id'].toString());
    if (_barberShopId == null && AuthService.instance.user?['is_admin'] != true) _barberShopId = AuthService.instance.user?['barber_shop_id'] as int?;
    } catch (_) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(shop_front_page_settingTr(context, 'form.could_not_load')), behavior: SnackBarBehavior.floating));
    } finally { if (mounted) setState(() => _loading = false); }
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
        const SizedBox(height: 18), Align(alignment: Alignment.centerLeft, child: Text(shop_front_page_settingTr(sheetContext, 'field.barber_shop_id'), style: const TextStyle(fontSize: 21, fontWeight: FontWeight.w800))),
        const SizedBox(height: 14), TextField(decoration: InputDecoration(prefixIcon: const Icon(Icons.search_rounded), hintText: shop_front_page_settingTr(sheetContext, 'form.search'), border: const OutlineInputBorder()), onChanged: (q) => setSheet(() => filtered = _barberShopOptions.where((e) => _displayName(e).toLowerCase().contains(q.toLowerCase())).toList())),
        const SizedBox(height: 12), Expanded(child: ListView.separated(itemCount: filtered.length, separatorBuilder: (_, __) => const Divider(height: 1), itemBuilder: (_, i) { final option = filtered[i]; return ListTile(title: Text(_displayName(option), style: const TextStyle(fontWeight: FontWeight.w700)), trailing: option['id'].toString() == _barberShopId?.toString() ? const Icon(Icons.check_circle_rounded) : null, onTap: () => Navigator.pop(sheetContext, option)); })),
      ])))),
    );
    if (selected != null) setState(() => _barberShopId = int.tryParse(selected['id'].toString()));
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
    payload['hero_title'] = _heroTitleController.text;
    payload['hero_subtitle'] = _heroSubtitleController.text;
    payload['hero_button_text'] = _heroButtonTextController.text;
    payload['services_badge_text'] = _servicesBadgeTextController.text;
    payload['services_title'] = _servicesTitleController.text;
    payload['staff_badge_text'] = _staffBadgeTextController.text;
    payload['staff_title'] = _staffTitleController.text;
    payload['contact_phone'] = _contactPhoneController.text;
    payload['contact_email'] = _contactEmailController.text;
    payload['contact_address'] = _contactAddressController.text;
    payload['google_maps_url'] = _googleMapsUrlController.text;
    payload['footer_text'] = _footerTextController.text;
    payload['barber_shop_id'] = _barberShopId;

    final files = <String, String>{};

    context.read<ShopFrontPageSettingCubit>().save(payload, id: widget.item?['id']);
  }

  @override Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return BlocProvider(
      create: (_) => ShopFrontPageSettingCubit(repository),
      child: BlocListener<ShopFrontPageSettingCubit, ShopFrontPageSettingState>(
        listener: (context, state) {
          if (state is ShopFrontPageSettingSaved) Navigator.pop(context, true);
          if (state is ShopFrontPageSettingFailure) {
            final message = state.message.trim().isEmpty ? shop_front_page_settingTr(context, 'form.save_error') : state.message;
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
            title: Text(widget.item == null ? shop_front_page_settingTr(context, 'form.create_title') : shop_front_page_settingTr(context, 'form.edit_title'), style: const TextStyle(fontWeight: FontWeight.w800)),
          ),
          body: _loading ? const Center(child: CircularProgressIndicator.adaptive()) : Form(key: _formKey, child: Builder(builder: (formContext) => ListView(padding: const EdgeInsets.fromLTRB(20, 12, 20, 120), children: [
            _FormHeader(isEdit: widget.item != null),
            const SizedBox(height: 22),
            _FieldShell(label: shop_front_page_settingTr(context, 'field.hero_title'), child: TextFormField(controller: _heroTitleController,  decoration: InputDecoration(hintText: shop_front_page_settingTr(context, 'field.hero_title'), border: InputBorder.none, isDense: true), validator: (v) {  return null; })),
            _FieldShell(label: shop_front_page_settingTr(context, 'field.hero_subtitle'), child: TextFormField(controller: _heroSubtitleController,  decoration: InputDecoration(hintText: shop_front_page_settingTr(context, 'field.hero_subtitle'), border: InputBorder.none, isDense: true), validator: (v) {  return null; })),
            _FieldShell(label: shop_front_page_settingTr(context, 'field.hero_button_text'), child: TextFormField(controller: _heroButtonTextController,  decoration: InputDecoration(hintText: shop_front_page_settingTr(context, 'field.hero_button_text'), border: InputBorder.none, isDense: true), validator: (v) {  return null; })),
            _FieldShell(label: shop_front_page_settingTr(context, 'field.services_badge_text'), child: TextFormField(controller: _servicesBadgeTextController,  decoration: InputDecoration(hintText: shop_front_page_settingTr(context, 'field.services_badge_text'), border: InputBorder.none, isDense: true), validator: (v) {  return null; })),
            _FieldShell(label: shop_front_page_settingTr(context, 'field.services_title'), child: TextFormField(controller: _servicesTitleController,  decoration: InputDecoration(hintText: shop_front_page_settingTr(context, 'field.services_title'), border: InputBorder.none, isDense: true), validator: (v) {  return null; })),
            _FieldShell(label: shop_front_page_settingTr(context, 'field.staff_badge_text'), child: TextFormField(controller: _staffBadgeTextController,  decoration: InputDecoration(hintText: shop_front_page_settingTr(context, 'field.staff_badge_text'), border: InputBorder.none, isDense: true), validator: (v) {  return null; })),
            _FieldShell(label: shop_front_page_settingTr(context, 'field.staff_title'), child: TextFormField(controller: _staffTitleController,  decoration: InputDecoration(hintText: shop_front_page_settingTr(context, 'field.staff_title'), border: InputBorder.none, isDense: true), validator: (v) {  return null; })),
            _FieldShell(label: shop_front_page_settingTr(context, 'field.contact_phone'), child: TextFormField(controller: _contactPhoneController,  decoration: InputDecoration(hintText: shop_front_page_settingTr(context, 'field.contact_phone'), border: InputBorder.none, isDense: true), validator: (v) {  return null; })),
            _FieldShell(label: shop_front_page_settingTr(context, 'field.contact_email'), child: TextFormField(controller: _contactEmailController,  decoration: InputDecoration(hintText: shop_front_page_settingTr(context, 'field.contact_email'), border: InputBorder.none, isDense: true), validator: (v) {  return null; })),
            _FieldShell(label: shop_front_page_settingTr(context, 'field.contact_address'), child: TextFormField(controller: _contactAddressController,  decoration: InputDecoration(hintText: shop_front_page_settingTr(context, 'field.contact_address'), border: InputBorder.none, isDense: true), validator: (v) {  return null; })),
            _FieldShell(label: shop_front_page_settingTr(context, 'field.google_maps_url'), child: TextFormField(controller: _googleMapsUrlController,  decoration: InputDecoration(hintText: shop_front_page_settingTr(context, 'field.google_maps_url'), border: InputBorder.none, isDense: true), validator: (v) {  return null; })),
            _FieldShell(label: shop_front_page_settingTr(context, 'field.footer_text'), child: TextFormField(controller: _footerTextController,  decoration: InputDecoration(hintText: shop_front_page_settingTr(context, 'field.footer_text'), border: InputBorder.none, isDense: true), validator: (v) {  return null; })),
            (AuthService.instance.user?['is_admin'] == true) 
              ? _FieldShell(label: shop_front_page_settingTr(context, 'field.barber_shop_id'), child: InkWell(onTap: _pickbarberShopId, borderRadius: BorderRadius.circular(16), child: Container(padding: const EdgeInsets.all(16), decoration: BoxDecoration(border: Border.all(color: Theme.of(context).colorScheme.outlineVariant), borderRadius: BorderRadius.circular(16)), child: Row(children: [Expanded(child: Text(_displayName(_barberShopOptions.firstWhere((e) => e['id'].toString() == _barberShopId?.toString(), orElse: () => {'id': '', 'name': shop_front_page_settingTr(context, 'form.select')})))), const Icon(Icons.keyboard_arrow_down_rounded)]))))
              : _FieldShell(label: shop_front_page_settingTr(context, 'field.barber_shop_id'), child: Container(padding: const EdgeInsets.all(16), width: double.infinity, decoration: BoxDecoration(color: theme.colorScheme.surfaceContainerHighest.withOpacity(0.5), border: Border.all(color: theme.colorScheme.outlineVariant), borderRadius: BorderRadius.circular(16)), child: Text(AuthService.instance.user?['business']?['name'] ?? '', style: const TextStyle(fontWeight: FontWeight.bold)))),

            const SizedBox(height: 14),
            BlocBuilder<ShopFrontPageSettingCubit, ShopFrontPageSettingState>(builder: (context, state) => PremiumButton(onPressed: () => _save(context), label: state is ShopFrontPageSettingSaving ? shop_front_page_settingTr(context, 'form.saving') : shop_front_page_settingTr(context, 'form.save'), icon: Icons.check_rounded, loading: state is ShopFrontPageSettingSaving, expand: true)),
          ]))),
        ),
      ),
    );
  }

  @override void dispose() {
    _heroTitleController.dispose();
    _heroSubtitleController.dispose();
    _heroButtonTextController.dispose();
    _servicesBadgeTextController.dispose();
    _servicesTitleController.dispose();
    _staffBadgeTextController.dispose();
    _staffTitleController.dispose();
    _contactPhoneController.dispose();
    _contactEmailController.dispose();
    _contactAddressController.dispose();
    _googleMapsUrlController.dispose();
    _footerTextController.dispose();
    super.dispose();
  }
}
class _FormHeader extends StatelessWidget {
  final bool isEdit;
  const _FormHeader({required this.isEdit});
  @override Widget build(BuildContext context) => Container(padding: const EdgeInsets.all(20), decoration: BoxDecoration(gradient: LinearGradient(colors: [Theme.of(context).colorScheme.primaryContainer, Theme.of(context).colorScheme.secondaryContainer]), borderRadius: BorderRadius.circular(24)), child: Row(children: [Container(width: 48, height: 48, decoration: BoxDecoration(color: Theme.of(context).colorScheme.surface.withOpacity(.7), borderRadius: BorderRadius.circular(15)), child: Icon(isEdit ? Icons.edit_rounded : Icons.add_rounded)), const SizedBox(width: 14), Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(isEdit ? shop_front_page_settingTr(context, 'form.update_record') : shop_front_page_settingTr(context, 'form.new_record'), style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900)), const SizedBox(height: 3), Text(isEdit ? shop_front_page_settingTr(context, 'form.review_update') : shop_front_page_settingTr(context, 'form.fill_create'), style: TextStyle(fontSize: 12, color: Theme.of(context).colorScheme.onSurfaceVariant))]))]));
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