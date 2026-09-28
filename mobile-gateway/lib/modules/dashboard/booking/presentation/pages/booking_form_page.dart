import 'dart:convert';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:image_picker/image_picker.dart';
import 'package:mobile_gateway/core/widgets/premium_widgets.dart';
import 'package:mobile_gateway/core/widgets/premium_image_picker.dart';
import 'package:mobile_gateway/l10n/booking_localization.dart';
import 'package:mobile_gateway/core/branding/branding_cubit.dart';
import '../cubit/booking_cubit.dart';
import '../cubit/booking_state.dart';
import '../../data/booking_repository.dart';
import 'package:mobile_gateway/services/api_service.dart';
import 'package:mobile_gateway/services/auth_service.dart';
import 'package:mobile_gateway/modules/dashboard/customer/presentation/pages/customer_form_page.dart';
import 'package:mobile_gateway/modules/dashboard/barber/presentation/pages/barber_form_page.dart';
import 'package:mobile_gateway/modules/dashboard/service/presentation/pages/service_form_page.dart';

class BookingFormPage extends StatefulWidget {
  final Map<String, dynamic>? item;
  const BookingFormPage({super.key, this.item});
  @override State<BookingFormPage> createState() => _BookingFormPageState();
}

class _BookingFormPageState extends State<BookingFormPage> {
  final _formKey = GlobalKey<FormState>();
  final repository = BookingRepository();
  bool _loading = true;

  final _appointmentAtController = TextEditingController();
  String? _status;
  final _totalPriceController = TextEditingController();
  final _notesController = TextEditingController();
  String? _source;

  List<Map<String, dynamic>> _barberShopOptions = [];
  int? _barberShopId;
  List<Map<String, dynamic>> _barberOptions = [];
  int? _barberId;
  List<Map<String, dynamic>> _serviceOptions = [];
  int? _serviceId;
  List<Map<String, dynamic>> _customerOptions = [];
  int? _customerId;


  @override void initState() { super.initState(); _init(); }

  Future<void> _init() async {
    _appointmentAtController.text = _displayDateTime(widget.item?['appointment_at'], includeTime: true);
    _status = widget.item?['status']?.toString() ?? 'pending';
    final _initialtotalPrice = widget.item?['total_price'];
    _totalPriceController.text = _initialtotalPrice == null ? '' : (double.tryParse(_initialtotalPrice.toString())?.toStringAsFixed(2) ?? _initialtotalPrice.toString());
    _notesController.text = widget.item?['notes']?.toString() ?? '';
    _source = widget.item?['source']?.toString() ?? 'walk-in';

    if (widget.item?['barber_id'] != null) {
      _barberId = int.tryParse(widget.item!['barber_id'].toString());
    }
    if (widget.item?['service_id'] != null) {
      _serviceId = int.tryParse(widget.item!['service_id'].toString());
    }
    if (widget.item?['customer_id'] != null) {
      _customerId = int.tryParse(widget.item!['customer_id'].toString());
    }
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
    } catch (_) {}

    try {
      _barberOptions = await repository.lookup('barbers');
    } catch (_) {}

    try {
      _serviceOptions = await repository.lookup('services');
    } catch (_) {}

    try {
      _customerOptions = await repository.lookup('customers');
    } catch (_) {}

    // Ensure preselected options exist in lookup lists so names are displayed immediately
    if (_barberId != null) {
      final exists = _barberOptions.any((b) => b['id'].toString() == _barberId.toString());
      if (!exists) {
        _barberOptions.insert(0, {
          'id': _barberId,
          'name': widget.item?['barber_name'] ?? 'Berber #$_barberId',
        });
      }
    }

    // Parse multi-services from notes if present (e.g. "Shërbimet: rroje + Rrojre qethje")
    final notesText = _notesController.text;
    if (notesText.startsWith("Shërbimet: ")) {
      final namesString = notesText.replaceFirst("Shërbimet: ", "").trim();
      final names = namesString.split('+').map((s) => s.trim()).where((s) => s.isNotEmpty).toList();

      for (final name in names) {
        final found = _serviceOptions.firstWhere(
          (s) => s['name']?.toString().trim().toLowerCase() == name.toLowerCase(),
          orElse: () => {},
        );
        if (found['id'] != null) {
          final foundId = int.tryParse(found['id'].toString());
          if (foundId != null && !_selectedServiceIds.contains(foundId)) {
            _selectedServiceIds.add(foundId);
          }
        }
      }
    }

    if (_selectedServiceIds.isEmpty && _serviceId != null) {
      _selectedServiceIds.add(_serviceId!);
    }

    if (_serviceId != null) {
      final exists = _serviceOptions.any((s) => s['id'].toString() == _serviceId.toString());
      if (!exists) {
        _serviceOptions.insert(0, {
          'id': _serviceId,
          'name': widget.item?['service_name'] ?? 'Shërbim #$_serviceId',
          'price': widget.item?['total_price'],
        });
      }
    }

    if (_customerId != null) {
      final exists = _customerOptions.any((c) => c['id'].toString() == _customerId.toString());
      if (!exists) {
        _customerOptions.insert(0, {
          'id': _customerId,
          'name': widget.item?['customer_name'] ?? 'Klient #$_customerId',
        });
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
      useSafeArea: true,
      backgroundColor: Theme.of(context).colorScheme.surface,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(30))),
      builder: (sheetContext) => SafeArea(
        child: StatefulBuilder(
          builder: (context, setSheet) => SizedBox(
            height: MediaQuery.of(context).size.height * .72,
            child: Padding(
              padding: EdgeInsets.fromLTRB(20, 20, 20, 20 + MediaQuery.of(sheetContext).padding.bottom),
              child: Column(children: [
                Container(width: 42, height: 4, decoration: BoxDecoration(color: Theme.of(context).dividerColor, borderRadius: BorderRadius.circular(10))),
                const SizedBox(height: 18), Align(alignment: Alignment.centerLeft, child: Text(bookingTr(sheetContext, 'field.barber_shop_id'), style: const TextStyle(fontSize: 21, fontWeight: FontWeight.w800))),
                const SizedBox(height: 14), TextField(decoration: InputDecoration(prefixIcon: const Icon(Icons.search_rounded), hintText: bookingTr(sheetContext, 'form.search'), border: const OutlineInputBorder()), onChanged: (q) => setSheet(() => filtered = _barberShopOptions.where((e) => _displayName(e).toLowerCase().contains(q.toLowerCase())).toList())),
                const SizedBox(height: 12), Expanded(child: ListView.separated(itemCount: filtered.length, separatorBuilder: (_, __) => const Divider(height: 1), itemBuilder: (_, i) { final option = filtered[i]; return ListTile(title: Text(_displayName(option), style: const TextStyle(fontWeight: FontWeight.w700)), trailing: option['id'].toString() == _barberShopId?.toString() ? const Icon(Icons.check_circle_rounded) : null, onTap: () => Navigator.pop(sheetContext, option)); })),
              ]),
            ),
          ),
        ),
      ),
    );
    if (selected != null) setState(() => _barberShopId = int.tryParse(selected['id'].toString()));
  }

  Future<void> _pickbarberId() async {
    if ("barber_id" == "barber_shop_id" && AuthService.instance.user?['is_admin'] != true) return;
    var filtered = List<Map<String, dynamic>>.from(_barberOptions);
    final selected = await showModalBottomSheet<Map<String, dynamic>>(
      context: context,
      isScrollControlled: true,
      useSafeArea: true,
      backgroundColor: Theme.of(context).colorScheme.surface,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(30))),
      builder: (sheetContext) => SafeArea(
        child: StatefulBuilder(
          builder: (context, setSheet) => SizedBox(
            height: MediaQuery.of(context).size.height * .75,
            child: Padding(
              padding: EdgeInsets.fromLTRB(20, 20, 20, 20 + MediaQuery.of(sheetContext).padding.bottom),
              child: Column(children: [
                Container(width: 42, height: 4, decoration: BoxDecoration(color: Theme.of(context).dividerColor, borderRadius: BorderRadius.circular(10))),
                const SizedBox(height: 18),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Expanded(child: Text(bookingTr(sheetContext, 'field.barber_id'), style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w800), overflow: TextOverflow.ellipsis)),
                    const SizedBox(width: 8),
                    TextButton.icon(
                      onPressed: () async {
                        final res = await Navigator.push<bool>(context, MaterialPageRoute(builder: (_) => const BarberFormPage()));
                        if (res == true) {
                          _barberOptions = await repository.lookup('barbers');
                          if (mounted) setSheet(() => filtered = List<Map<String, dynamic>>.from(_barberOptions));
                        }
                      },
                      icon: const Icon(Icons.add_circle_outline_rounded, size: 18),
                      label: Text('Shto ${context.staffLabel}', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                    ),
                  ],
                ),
                const SizedBox(height: 14),
                TextField(decoration: InputDecoration(prefixIcon: const Icon(Icons.search_rounded), hintText: bookingTr(sheetContext, 'form.search'), border: const OutlineInputBorder()), onChanged: (q) => setSheet(() => filtered = _barberOptions.where((e) => _displayName(e).toLowerCase().contains(q.toLowerCase())).toList())),
                const SizedBox(height: 12),
                Expanded(child: ListView.separated(itemCount: filtered.length, separatorBuilder: (_, __) => const Divider(height: 1), itemBuilder: (_, i) { final option = filtered[i]; return ListTile(title: Text(_displayName(option), style: const TextStyle(fontWeight: FontWeight.w700)), trailing: option['id'].toString() == _barberId?.toString() ? const Icon(Icons.check_circle_rounded) : null, onTap: () => Navigator.pop(sheetContext, option)); })),
              ]),
            ),
          ),
        ),
      ),
    );
    if (selected != null) setState(() => _barberId = int.tryParse(selected['id'].toString()));
  }

  List<int> _selectedServiceIds = [];

  Future<void> _pickserviceId() async {
    var filtered = List<Map<String, dynamic>>.from(_serviceOptions);
    List<int> tempSelectedIds = List<int>.from(_selectedServiceIds);
    if (tempSelectedIds.isEmpty && _serviceId != null) {
      tempSelectedIds.add(_serviceId!);
    }

    await showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      useSafeArea: true,
      backgroundColor: Theme.of(context).colorScheme.surface,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(30))),
      builder: (sheetContext) => SafeArea(
        child: StatefulBuilder(
          builder: (context, setSheet) {
            double totalPrice = 0;
            int totalDuration = 0;
            final selectedNames = <String>[];

            for (final id in tempSelectedIds) {
              final opt = _serviceOptions.firstWhere((e) => e['id'].toString() == id.toString(), orElse: () => {});
              final price = double.tryParse(opt['price']?.toString() ?? '0') ?? 0;
              final dur = int.tryParse(opt['duration_minutes']?.toString() ?? '30') ?? 30;
              totalPrice += price;
              totalDuration += dur;
              if (opt['name'] != null) selectedNames.add(opt['name'].toString());
            }

            return SizedBox(
              height: MediaQuery.of(context).size.height * 0.78,
              child: Padding(
                padding: EdgeInsets.fromLTRB(20, 20, 20, 20 + MediaQuery.of(sheetContext).padding.bottom),
                child: Column(
                  children: [
                    Container(width: 42, height: 4, decoration: BoxDecoration(color: Theme.of(context).dividerColor, borderRadius: BorderRadius.circular(10))),
                    const SizedBox(height: 18),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(bookingTr(sheetContext, 'field.service_id'), style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w800)),
                              if (tempSelectedIds.isNotEmpty)
                                Text("${tempSelectedIds.length} shërbime zgjedhur ($totalDuration min)", style: TextStyle(fontSize: 12, color: Theme.of(context).colorScheme.primary, fontWeight: FontWeight.bold)),
                            ],
                          ),
                        ),
                        TextButton.icon(
                          onPressed: () async {
                            final res = await Navigator.push<bool>(context, MaterialPageRoute(builder: (_) => const ServiceFormPage()));
                            if (res == true) {
                              _serviceOptions = await repository.lookup('services');
                              if (mounted) setSheet(() => filtered = List<Map<String, dynamic>>.from(_serviceOptions));
                            }
                          },
                          icon: const Icon(Icons.add_circle_outline_rounded, size: 18),
                          label: const Text('Shto Shërbim', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                        ),
                      ],
                    ),
                    const SizedBox(height: 14),
                    TextField(
                      decoration: InputDecoration(
                        prefixIcon: const Icon(Icons.search_rounded),
                        hintText: bookingTr(sheetContext, 'form.search'),
                        border: const OutlineInputBorder(),
                      ),
                      onChanged: (q) => setSheet(() => filtered = _serviceOptions.where((e) => _displayName(e).toLowerCase().contains(q.toLowerCase())).toList()),
                    ),
                    const SizedBox(height: 12),
                    Expanded(
                      child: ListView.separated(
                        itemCount: filtered.length,
                        separatorBuilder: (_, __) => const Divider(height: 1),
                        itemBuilder: (_, i) {
                          final option = filtered[i];
                          final optionId = int.tryParse(option['id'].toString());
                          final isSelected = optionId != null && tempSelectedIds.contains(optionId);

                          return CheckboxListTile(
                            value: isSelected,
                            title: Text(_displayName(option), style: const TextStyle(fontWeight: FontWeight.w700)),
                            subtitle: Text("${option['price'] ?? '0.00'} Lekë (${option['duration_minutes'] ?? '30'} min)"),
                            activeColor: Theme.of(context).colorScheme.primary,
                            onChanged: (checked) {
                              setSheet(() {
                                if (optionId != null) {
                                  if (checked == true) {
                                    if (!tempSelectedIds.contains(optionId)) tempSelectedIds.add(optionId);
                                  } else {
                                    tempSelectedIds.remove(optionId);
                                  }
                                }
                              });
                            },
                          );
                        },
                      ),
                    ),
                    const SizedBox(height: 12),
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton.icon(
                        onPressed: tempSelectedIds.isEmpty ? null : () {
                          setState(() {
                            _selectedServiceIds = List<int>.from(tempSelectedIds);
                            _serviceId = _selectedServiceIds.isNotEmpty ? _selectedServiceIds.first : null;
                            if (totalPrice > 0 && (widget.item == null || _totalPriceController.text.isEmpty)) {
                              _totalPriceController.text = totalPrice.toStringAsFixed(2);
                            }
                            if (selectedNames.isNotEmpty) {
                              _notesController.text = "Shërbimet: ${selectedNames.join(' + ')}";
                            }
                          });
                          _validateCurrentSlotForService();
                          Navigator.pop(sheetContext);
                        },
                        icon: const Icon(Icons.check_circle_rounded),
                        label: Text(
                          tempSelectedIds.isEmpty
                              ? 'Zgjidh të paktën 1 shërbim'
                              : 'Konfirmo (${totalPrice.toStringAsFixed(2)} Lekë - $totalDuration min)',
                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                        ),
                        style: ElevatedButton.styleFrom(
                          padding: const EdgeInsets.symmetric(vertical: 14),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            );
          },
        ),
      ),
    );
  }

  Future<void> _pickcustomerId() async {
    if ("customer_id" == "barber_shop_id" && AuthService.instance.user?['is_admin'] != true) return;
    var filtered = List<Map<String, dynamic>>.from(_customerOptions);
    final selected = await showModalBottomSheet<Map<String, dynamic>>(
      context: context,
      isScrollControlled: true,
      useSafeArea: true,
      backgroundColor: Theme.of(context).colorScheme.surface,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(30))),
      builder: (sheetContext) => SafeArea(
        child: StatefulBuilder(
          builder: (context, setSheet) => SizedBox(
            height: MediaQuery.of(context).size.height * .75,
            child: Padding(
              padding: EdgeInsets.fromLTRB(20, 20, 20, 20 + MediaQuery.of(sheetContext).padding.bottom),
              child: Column(children: [
                Container(width: 42, height: 4, decoration: BoxDecoration(color: Theme.of(context).dividerColor, borderRadius: BorderRadius.circular(10))),
                const SizedBox(height: 18),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Expanded(child: Text(bookingTr(sheetContext, 'field.customer_id'), style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w800), overflow: TextOverflow.ellipsis)),
                    const SizedBox(width: 8),
                    TextButton.icon(
                      onPressed: () async {
                        final newCust = await Navigator.push<bool>(context, MaterialPageRoute(builder: (_) => const CustomerFormPage()));
                        if (newCust == true) {
                          _customerOptions = await repository.lookup('customers');
                          if (mounted) {
                            setSheet(() {
                              filtered = List<Map<String, dynamic>>.from(_customerOptions);
                            });
                          }
                        }
                      },
                      icon: const Icon(Icons.add_circle_outline_rounded, size: 18),
                      label: const Text('Shto Klient', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                    ),
                  ],
                ),
                const SizedBox(height: 14),
                TextField(decoration: InputDecoration(prefixIcon: const Icon(Icons.search_rounded), hintText: bookingTr(sheetContext, 'form.search'), border: const OutlineInputBorder()), onChanged: (q) => setSheet(() => filtered = _customerOptions.where((e) => _displayName(e).toLowerCase().contains(q.toLowerCase())).toList())),
                const SizedBox(height: 12),
                Expanded(child: ListView.separated(itemCount: filtered.length, separatorBuilder: (_, __) => const Divider(height: 1), itemBuilder: (_, i) { final option = filtered[i]; return ListTile(title: Text(_displayName(option), style: const TextStyle(fontWeight: FontWeight.w700)), trailing: option['id'].toString() == _customerId?.toString() ? const Icon(Icons.check_circle_rounded) : null, onTap: () => Navigator.pop(sheetContext, option)); })),
              ]),
            ),
          ),
        ),
      ),
    );
    if (selected != null) setState(() => _customerId = int.tryParse(selected['id'].toString()));
  }

  int _totalSelectedDuration() {
    if (_selectedServiceIds.isNotEmpty) {
      int sum = 0;
      for (final id in _selectedServiceIds) {
        final opt = _serviceOptions.firstWhere((e) => e['id'].toString() == id.toString(), orElse: () => {});
        final dur = int.tryParse(opt['duration_minutes']?.toString() ?? '30') ?? 30;
        sum += dur;
      }
      if (sum > 0) return sum;
    }
    if (_serviceId != null) {
      final opt = _serviceOptions.firstWhere((e) => e['id'].toString() == _serviceId.toString(), orElse: () => {});
      return int.tryParse(opt['duration_minutes']?.toString() ?? '30') ?? 30;
    }
    return 30;
  }

  String? _slotWarning;

  Future<void> _validateCurrentSlotForService() async {
    if (_appointmentAtController.text.isEmpty || _serviceId == null) return;
    final dt = _parseDisplayDate(_appointmentAtController.text);
    if (dt == null) return;

    final dateStr = "${dt.year}-${dt.month.toString().padLeft(2, '0')}-${dt.day.toString().padLeft(2, '0')}";
    final timeStr = "${dt.hour.toString().padLeft(2, '0')}:${dt.minute.toString().padLeft(2, '0')}";

    try {
      final ignoreId = widget.item?['id'] ?? '';
      final res = await ApiService.get('/bookings/day-schedule?date=$dateStr&barber_id=${_barberId ?? ''}&service_id=$_serviceId&ignore_booking_id=$ignoreId');
      if (res.statusCode == 200) {
        final body = jsonDecode(res.body);
        final slots = (body['daySlots'] as List? ?? []);
        final currentSlot = slots.firstWhere((s) => s['time'].toString() == timeStr, orElse: () => null);

        if (currentSlot != null) {
          if (currentSlot['is_free'] != true) {
            setState(() {
              _slotWarning = "Orari ($timeStr) është i zënë. Zgjidhni një orar të lirë.";
            });
          } else {
            final availMins = int.tryParse(currentSlot['available_minutes']?.toString() ?? '999') ?? 999;
            final reqMins = _totalSelectedDuration();
            if (reqMins > availMins) {
              setState(() {
                _slotWarning = "Orari ($timeStr) ka vetëm $availMins min të lira para rezervimit tjetër, ndërsa shërbimet kërkojnë $reqMins min.";
              });
            } else {
              setState(() {
                _slotWarning = null;
              });
            }
          }
        } else {
          setState(() {
            _slotWarning = null;
          });
        }
      }
    } catch (_) {}
  }

  Future<void> _pickAvailableSlot() async {
    DateTime selectedDate = _parseDisplayDate(_appointmentAtController.text) ?? DateTime.now();

    await showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      useSafeArea: true,
      backgroundColor: Theme.of(context).colorScheme.surface,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(30))),
      builder: (sheetContext) {
        final theme = Theme.of(context);
        final isDark = theme.brightness == Brightness.dark;

        return SafeArea(
          child: StatefulBuilder(
            builder: (context, setSheetState) {
              final dateStr = "${selectedDate.year}-${selectedDate.month.toString().padLeft(2, '0')}-${selectedDate.day.toString().padLeft(2, '0')}";

              return SizedBox(
                height: MediaQuery.of(context).size.height * 0.75,
                child: Padding(
                  padding: EdgeInsets.fromLTRB(20, 20, 20, 20 + MediaQuery.of(sheetContext).padding.bottom),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Center(
                        child: Container(width: 42, height: 4, decoration: BoxDecoration(color: isDark ? const Color(0xFF4B5563) : Theme.of(context).dividerColor, borderRadius: BorderRadius.circular(10))),
                      ),
                      const SizedBox(height: 18),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text('Zgjidh Orarin e Lirë', style: TextStyle(fontSize: 20, fontWeight: FontWeight.w900, color: isDark ? Colors.white : null)),
                          OutlinedButton.icon(
                            onPressed: () async {
                              final picked = await showDatePicker(
                                context: context,
                                initialDate: selectedDate,
                                firstDate: DateTime.now().subtract(const Duration(days: 1)),
                                lastDate: DateTime.now().add(const Duration(days: 365)),
                              );
                              if (picked != null) {
                                setSheetState(() => selectedDate = picked);
                              }
                            },
                            icon: Icon(Icons.calendar_month_rounded, size: 16, color: isDark ? theme.colorScheme.primary : null),
                            label: Text("${selectedDate.day}/${selectedDate.month}/${selectedDate.year}", style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: isDark ? Colors.white : null)),
                            style: OutlinedButton.styleFrom(
                              side: BorderSide(color: isDark ? const Color(0xFF3B82F6) : theme.colorScheme.outlineVariant),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      Text('Orare të lira për këtë shërbim (${_serviceDurationText()}):', style: TextStyle(fontSize: 12.5, color: isDark ? const Color(0xFFCBD5E1) : theme.colorScheme.onSurfaceVariant, fontWeight: FontWeight.w600)),
                      const SizedBox(height: 16),
                      Expanded(
                        child: FutureBuilder<dynamic>(
                          future: ApiService.get('/bookings/day-schedule?date=$dateStr&barber_id=${_barberId ?? ''}&service_id=${_serviceId ?? ''}'),
                          builder: (context, snapshot) {
                            if (snapshot.connectionState == ConnectionState.waiting) {
                              return const Center(child: CircularProgressIndicator.adaptive());
                            }
                            if (snapshot.hasError) {
                              return Center(child: Text('Gabim: ${snapshot.error}'));
                            }
                            final res = snapshot.data;
                            if (res == null || res.statusCode != 200) {
                              return const Center(child: Text('Nuk u ngarkuan oraret.'));
                            }
                            final body = jsonDecode(res.body);
                            final slots = (body['daySlots'] as List? ?? []);
                            final freeSlots = slots.where((s) => s['is_free'] == true).toList();

                            if (freeSlots.isEmpty) {
                              return Center(
                                child: Column(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: [
                                    const Icon(Icons.event_busy_rounded, size: 48, color: Colors.grey),
                                    const SizedBox(height: 12),
                                    Text('Nuk ka orare të lira me këtë kohëzgjatje për këtë datë.', textAlign: TextAlign.center, style: TextStyle(fontWeight: FontWeight.bold, color: isDark ? Colors.white : null)),
                                    const SizedBox(height: 16),
                                    ElevatedButton(
                                      onPressed: () async {
                                        final picked = await showDatePicker(
                                          context: context,
                                          initialDate: selectedDate.add(const Duration(days: 1)),
                                          firstDate: DateTime.now(),
                                          lastDate: DateTime.now().add(const Duration(days: 365)),
                                        );
                                        if (picked != null) {
                                          setSheetState(() => selectedDate = picked);
                                        }
                                      },
                                      child: const Text('Zgjidh një datë tjetër'),
                                    ),
                                  ],
                                ),
                              );
                            }

                            return GridView.builder(
                              gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                                crossAxisCount: 3,
                                childAspectRatio: 2.3,
                                crossAxisSpacing: 10,
                                mainAxisSpacing: 10,
                              ),
                              itemCount: freeSlots.length,
                              itemBuilder: (context, i) {
                                final slot = freeSlots[i];
                                final timeStr = slot['time'].toString();
                                return InkWell(
                                  onTap: () {
                                    final dt = DateTime(selectedDate.year, selectedDate.month, selectedDate.day, int.parse(timeStr.split(':')[0]), int.parse(timeStr.split(':')[1]));
                                    setState(() {
                                      _appointmentAtController.text = _formatDisplayDate(dt, includeTime: true);
                                    });
                                    _validateCurrentSlotForService();
                                    Navigator.pop(sheetContext);
                                  },
                                  borderRadius: BorderRadius.circular(12),
                                  child: Container(
                                    decoration: BoxDecoration(
                                      color: isDark ? const Color(0xFF1E2638) : theme.colorScheme.primaryContainer.withOpacity(0.3),
                                      borderRadius: BorderRadius.circular(12),
                                      border: Border.all(
                                        color: isDark ? const Color(0xFF3B82F6) : theme.colorScheme.primary,
                                        width: 1.2,
                                      ),
                                    ),
                                    alignment: Alignment.center,
                                    child: Text(
                                      timeStr,
                                      style: TextStyle(
                                        fontWeight: FontWeight.w900,
                                        color: isDark ? Colors.white : theme.colorScheme.primary,
                                        fontSize: 15.5,
                                        letterSpacing: 0.5,
                                      ),
                                    ),
                                  ),
                                );
                              },
                            );
                          },
                        ),
                      ),
                      const SizedBox(height: 12),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          TextButton.icon(
                            onPressed: () {
                              Navigator.pop(sheetContext);
                              _pickDateTime(_appointmentAtController);
                            },
                            icon: const Icon(Icons.edit_calendar_rounded, size: 16),
                            label: const Text('Përzgjidh orar manualisht', style: TextStyle(fontSize: 12)),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              );
            },
          ),
        );
      },
    );
  }

  String _serviceDurationText() {
    final serv = _serviceOptions.firstWhere((e) => e['id'].toString() == _serviceId?.toString(), orElse: () => {});
    final dur = serv['duration_minutes'] ?? 30;
    return "$dur min";
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

  String _selectedServiceDisplay() {
    if (_selectedServiceIds.isEmpty && _serviceId == null) {
      return bookingTr(context, 'form.select');
    }

    final names = <String>[];
    for (final id in _selectedServiceIds) {
      final opt = _serviceOptions.firstWhere((e) => e['id'].toString() == id.toString(), orElse: () => {});
      if (opt['name'] != null && opt['name'].toString().isNotEmpty) {
        names.add(opt['name'].toString());
      }
    }

    if (names.isEmpty && _serviceId != null) {
      final opt = _serviceOptions.firstWhere((e) => e['id'].toString() == _serviceId.toString(), orElse: () => {});
      if (opt['name'] != null && opt['name'].toString().isNotEmpty) {
        names.add(opt['name'].toString());
      }
    }

    if (names.isEmpty) {
      return widget.item?['service_name'] ?? bookingTr(context, 'form.select');
    }

    if (names.length > 1) {
      return "${names.join(' + ')} (${names.length} shërbime)";
    }
    return names.first;
  }

  Future<void> _save(BuildContext context) async {
    if (!_formKey.currentState!.validate()) return;
    final payload = <String, dynamic>{};
    payload['barber_shop_id'] = _barberShopId;
    payload['barber_id'] = _barberId;
    payload['service_id'] = _serviceId;
    payload['customer_id'] = _customerId;
    payload['appointment_at'] = _appointmentAtController.text.isEmpty ? null : _apiDateTimeValue(_appointmentAtController.text);
    payload['status'] = _status;
    payload['total_price'] = double.tryParse(_totalPriceController.text);
    payload['notes'] = _notesController.text;
    payload['source'] = _source;

    final files = <String, String>{};

    context.read<BookingCubit>().save(payload, id: widget.item?['id']);
  }

  Future<void> _showLunchWarningDialog(BuildContext parentContext, String message) async {
    final payload = <String, dynamic>{};
    payload['barber_shop_id'] = _barberShopId;
    payload['barber_id'] = _barberId;
    payload['service_id'] = _serviceId;
    payload['customer_id'] = _customerId;
    payload['appointment_at'] = _appointmentAtController.text.isEmpty ? null : _apiDateTimeValue(_appointmentAtController.text);
    payload['status'] = _status;
    payload['total_price'] = double.tryParse(_totalPriceController.text);
    payload['notes'] = _notesController.text;
    payload['source'] = _source;
    payload['override_lunch'] = true;

    final confirm = await showDialog<bool>(
      context: parentContext,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Row(
          children: [
            Icon(Icons.warning_amber_rounded, color: Colors.orange, size: 28),
            SizedBox(width: 8),
            Text('Kujdes: Orar Pushimi', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
          ],
        ),
        content: Text(
          "$message\n\nA jeni daktort të vazhdoni me këtë orar?",
          style: const TextStyle(fontSize: 14),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Zgjidh orar tjetër', style: TextStyle(color: Colors.grey, fontWeight: FontWeight.bold)),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.orange,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            ),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Po, Ruaj Gjithsesi', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );

    if (confirm == true && mounted) {
      parentContext.read<BookingCubit>().save(payload, id: widget.item?['id']);
    }
  }

  @override Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return BlocProvider(
      create: (_) => BookingCubit(repository),
      child: BlocListener<BookingCubit, BookingState>(
        listener: (context, state) {
          if (state is BookingSaved) Navigator.pop(context, true);
          if (state is BookingFailure) {
            final message = state.message.trim().isEmpty ? bookingTr(context, 'form.save_error') : state.message;
            if (message.toLowerCase().contains('pushimi') || message.toLowerCase().contains('dreka') || message.toLowerCase().contains('lunch')) {
              _showLunchWarningDialog(context, message);
            } else {
              ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(message), behavior: SnackBarBehavior.floating, duration: const Duration(seconds: 4)));
            }
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
            title: Text(widget.item?['id'] == null ? bookingTr(context, 'form.create_title') : bookingTr(context, 'form.edit_title'), style: const TextStyle(fontWeight: FontWeight.w800)),
          ),
          body: _loading ? const Center(child: CircularProgressIndicator.adaptive()) : Form(key: _formKey, child: Builder(builder: (formContext) => ListView(padding: const EdgeInsets.fromLTRB(20, 12, 20, 120), children: [
            const SizedBox(height: 12),
            (AuthService.instance.user?['is_admin'] == true)
              ? _FieldShell(label: bookingTr(context, 'field.barber_shop_id'), child: InkWell(onTap: _pickbarberShopId, borderRadius: BorderRadius.circular(16), child: Container(padding: const EdgeInsets.all(16), decoration: BoxDecoration(border: Border.all(color: Theme.of(context).colorScheme.outlineVariant), borderRadius: BorderRadius.circular(16)), child: Row(children: [Expanded(child: Text(_displayName(_barberShopOptions.firstWhere((e) => e['id'].toString() == _barberShopId?.toString(), orElse: () => {'id': '', 'name': bookingTr(context, 'form.select')})))), const Icon(Icons.keyboard_arrow_down_rounded)]))))
              : _FieldShell(label: bookingTr(context, 'field.barber_shop_id'), child: Container(padding: const EdgeInsets.all(16), width: double.infinity, decoration: BoxDecoration(color: theme.colorScheme.surfaceContainerHighest.withOpacity(0.5), border: Border.all(color: theme.colorScheme.outlineVariant), borderRadius: BorderRadius.circular(16)), child: Text(AuthService.instance.user?['business']?['name'] ?? '', style: const TextStyle(fontWeight: FontWeight.bold)))),
            _FieldShell(label: bookingTr(context, 'field.barber_id'), child: InkWell(onTap: _pickbarberId, borderRadius: BorderRadius.circular(16), child: Container(padding: const EdgeInsets.all(16), decoration: BoxDecoration(border: Border.all(color: Theme.of(context).colorScheme.outlineVariant), borderRadius: BorderRadius.circular(16)), child: Row(children: [Expanded(child: Text(_displayName(_barberOptions.firstWhere((e) => e['id'].toString() == _barberId?.toString(), orElse: () => {'id': '', 'name': bookingTr(context, 'form.select')})))), const Icon(Icons.keyboard_arrow_down_rounded)])))),
            _FieldShell(label: bookingTr(context, 'field.service_id'), child: InkWell(onTap: _pickserviceId, borderRadius: BorderRadius.circular(16), child: Container(padding: const EdgeInsets.all(16), decoration: BoxDecoration(border: Border.all(color: Theme.of(context).colorScheme.outlineVariant), borderRadius: BorderRadius.circular(16)), child: Row(children: [Expanded(child: Text(_selectedServiceDisplay(), style: const TextStyle(fontWeight: FontWeight.bold))), const Icon(Icons.keyboard_arrow_down_rounded)])))),
            _FieldShell(label: bookingTr(context, 'field.customer_id'), child: InkWell(onTap: _pickcustomerId, borderRadius: BorderRadius.circular(16), child: Container(padding: const EdgeInsets.all(16), decoration: BoxDecoration(border: Border.all(color: Theme.of(context).colorScheme.outlineVariant), borderRadius: BorderRadius.circular(16)), child: Row(children: [Expanded(child: Text(_displayName(_customerOptions.firstWhere((e) => e['id'].toString() == _customerId?.toString(), orElse: () => {'id': '', 'name': bookingTr(context, 'form.select')})))), const Icon(Icons.keyboard_arrow_down_rounded)])))),
            _FieldShell(label: bookingTr(context, 'field.appointment_at'), child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              InkWell(onTap: _pickAvailableSlot, borderRadius: BorderRadius.circular(12), child: InputDecorator(decoration: InputDecoration(border: InputBorder.none, isDense: true, suffixIcon: const Icon(Icons.event_available_rounded)), child: Text(_appointmentAtController.text.isEmpty ? bookingTr(context, 'form.select_datetime') : _appointmentAtController.text, style: TextStyle(color: _appointmentAtController.text.isEmpty ? Theme.of(context).colorScheme.onSurfaceVariant : null, fontWeight: FontWeight.bold, fontSize: 15)))),
              if (_slotWarning != null) ...[
                const SizedBox(height: 6),
                InkWell(
                  onTap: _pickAvailableSlot,
                  borderRadius: BorderRadius.circular(12),
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                    decoration: BoxDecoration(color: Colors.red.withOpacity(0.12), borderRadius: BorderRadius.circular(10), border: Border.all(color: Colors.red.withOpacity(0.5))),
                    child: Row(children: [
                      const Icon(Icons.warning_amber_rounded, color: Colors.red, size: 16),
                      const SizedBox(width: 6),
                      Expanded(child: Text(_slotWarning!, style: const TextStyle(color: Colors.red, fontSize: 11.5, fontWeight: FontWeight.bold))),
                    ]),
                  ),
                ),
              ],
            ])),
            _FieldShell(label: bookingTr(context, 'field.status'), child: DropdownButtonFormField<String>(value: ['pending', 'confirmed', 'completed', 'cancelled', 'no-show'].contains(_status) ? _status : null, items: ['pending', 'confirmed', 'completed', 'cancelled', 'no-show'].map((v) => DropdownMenuItem<String>(value: v, child: Text(v))).toList(), onChanged: (v) => setState(() => _status = v), validator: (v) { if (v == null || v.isEmpty) return bookingTr(context, 'form.select'); return null; }, decoration: const InputDecoration(border: InputBorder.none, isDense: true))),
            _FieldShell(label: bookingTr(context, 'field.total_price'), child: TextFormField(controller: _totalPriceController, keyboardType: const TextInputType.numberWithOptions(decimal: true), inputFormatters: [TextInputFormatter.withFunction((oldValue, newValue) { final text = newValue.text; if (text.isEmpty || RegExp(r'^\d*\.?\d{0,2}$').hasMatch(text)) return newValue; return oldValue; })],  decoration: InputDecoration(hintText: bookingTr(context, 'field.total_price'), border: InputBorder.none, isDense: true), validator: (v) { if (v == null || v.trim().isEmpty) return bookingTr(context, 'form.required'); if (v != null && v.isNotEmpty && (double.tryParse(v) == null || !RegExp(r'^\d+(\.\d{1,2})?$').hasMatch(v))) return 'Enter a valid number with up to 2 decimals';  return null; })),
            _FieldShell(label: bookingTr(context, 'field.notes'), child: TextFormField(controller: _notesController,  decoration: InputDecoration(hintText: bookingTr(context, 'field.notes'), border: InputBorder.none, isDense: true), validator: (v) {  return null; })),
            _FieldShell(label: bookingTr(context, 'field.source'), child: DropdownButtonFormField<String>(value: ['online', 'walk-in', 'phone'].contains(_source) ? _source : null, items: ['online', 'walk-in', 'phone'].map((v) => DropdownMenuItem<String>(value: v, child: Text(v))).toList(), onChanged: (v) => setState(() => _source = v), validator: (v) { if (v == null || v.isEmpty) return bookingTr(context, 'form.select'); return null; }, decoration: const InputDecoration(border: InputBorder.none, isDense: true))),

            const SizedBox(height: 14),
            BlocBuilder<BookingCubit, BookingState>(builder: (context, state) => PremiumButton(onPressed: () => _save(context), label: state is BookingSaving ? bookingTr(context, 'form.saving') : bookingTr(context, 'form.save'), icon: Icons.check_rounded, loading: state is BookingSaving, expand: true)),
          ]))),
        ),
      ),
    );
  }

  @override void dispose() {
    _appointmentAtController.dispose();
    _totalPriceController.dispose();
    _notesController.dispose();
    super.dispose();
  }
}
class _FormHeader extends StatelessWidget {
  final bool isEdit;
  const _FormHeader({required this.isEdit});
  @override Widget build(BuildContext context) => Container(padding: const EdgeInsets.all(20), decoration: BoxDecoration(gradient: LinearGradient(colors: [Theme.of(context).colorScheme.primaryContainer, Theme.of(context).colorScheme.secondaryContainer]), borderRadius: BorderRadius.circular(24)), child: Row(children: [Container(width: 48, height: 48, decoration: BoxDecoration(color: Theme.of(context).colorScheme.surface.withOpacity(.7), borderRadius: BorderRadius.circular(15)), child: Icon(isEdit ? Icons.edit_calendar_rounded : Icons.add_business_rounded)), const SizedBox(width: 14), Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(isEdit ? bookingTr(context, 'form.update_record') : bookingTr(context, 'form.new_record'), style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900)), const SizedBox(height: 3), Text(isEdit ? bookingTr(context, 'form.review_update') : bookingTr(context, 'form.fill_create'), style: TextStyle(fontSize: 12, color: Theme.of(context).colorScheme.onSurfaceVariant))]))]));
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
