import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:http/http.dart' as http;
import 'package:mobile_gateway/core/widgets/premium_widgets.dart';
import 'package:mobile_gateway/l10n/booking_localization.dart';
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
  bool _sendSms = true;

  List<Map<String, dynamic>> _barberShopOptions = [];
  int? _barberShopId;
  List<Map<String, dynamic>> _barberOptions = [];
  int? _barberId;
  List<Map<String, dynamic>> _serviceOptions = [];
  int? _serviceId;
  List<int> _selectedServiceIds = [];
  List<Map<String, dynamic>> _customerOptions = [];
  int? _customerId;

  @override void initState() { super.initState(); _init(); }

  Future<void> _init() async {
    _appointmentAtController.text = _displayDateTime(widget.item?['appointment_at'], includeTime: true);
    _status = widget.item?['status']?.toString() ?? 'pending';
    final initialTotalPrice = widget.item?['total_price'];
    _totalPriceController.text = initialTotalPrice == null ? '' : (double.tryParse(initialTotalPrice.toString())?.toStringAsFixed(2) ?? initialTotalPrice.toString());
    _notesController.text = widget.item?['notes']?.toString() ?? '';
    _source = widget.item?['source']?.toString() ?? 'walk-in';

    if (widget.item?['barber_id'] != null) {
      _barberId = int.tryParse(widget.item!['barber_id'].toString());
    }
    if (widget.item?['service_id'] != null) {
      _serviceId = int.tryParse(widget.item!['service_id'].toString());
      if (_serviceId != null && !_selectedServiceIds.contains(_serviceId)) {
        _selectedServiceIds.add(_serviceId!);
      }
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

    // Preselect barber if only 1 available or matching
    if (_barberId == null && _barberOptions.isNotEmpty) {
      _barberId = int.tryParse(_barberOptions.first['id']?.toString() ?? '');
    }

    // Parse multi-services from notes if present
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

    if (mounted) {
      setState(() => _loading = false);
    }
  }

  void _toggleServiceChip(int id) {
    setState(() {
      if (_selectedServiceIds.contains(id)) {
        _selectedServiceIds.remove(id);
      } else {
        _selectedServiceIds.add(id);
      }
      _serviceId = _selectedServiceIds.isNotEmpty ? _selectedServiceIds.first : null;

      double total = 0;
      final selectedNames = <String>[];
      for (final sId in _selectedServiceIds) {
        final opt = _serviceOptions.firstWhere((e) => e['id'].toString() == sId.toString(), orElse: () => {});
        final price = double.tryParse(opt['price']?.toString() ?? '0') ?? 0;
        total += price;
        if (opt['name'] != null) selectedNames.add(opt['name'].toString());
      }

      if (total > 0) {
        _totalPriceController.text = total.toStringAsFixed(2);
      }

      if (selectedNames.isNotEmpty) {
        _notesController.text = "Shërbimet: ${selectedNames.join(' + ')}";
      } else {
        _notesController.text = '';
      }
    });
    _validateCurrentSlotForService();
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
        const SizedBox(height: 18), Align(alignment: Alignment.centerLeft, child: Text(bookingTr(sheetContext, 'field.barber_shop_id'), style: const TextStyle(fontSize: 21, fontWeight: FontWeight.w800))),
        const SizedBox(height: 14), TextField(decoration: InputDecoration(prefixIcon: const Icon(Icons.search_rounded), hintText: bookingTr(sheetContext, 'form.search'), border: const OutlineInputBorder()), onChanged: (q) => setSheet(() => filtered = _barberShopOptions.where((e) => _displayName(e).toLowerCase().contains(q.toLowerCase())).toList())),
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
                        final newBarber = await Navigator.push<bool>(context, MaterialPageRoute(builder: (_) => const BarberFormPage()));
                        if (newBarber == true) {
                          _barberOptions = await repository.lookup('barbers');
                          if (mounted) setSheet(() => filtered = List<Map<String, dynamic>>.from(_barberOptions));
                        }
                      },
                      icon: const Icon(Icons.add_circle_outline_rounded, size: 18),
                      label: const Text('Shto Staf', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
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
    if (selected != null) {
      setState(() => _barberId = int.tryParse(selected['id'].toString()));
      _validateCurrentSlotForService();
    }
  }

  Future<void> _pickserviceId() async {
    final newService = await Navigator.push<bool>(context, MaterialPageRoute(builder: (_) => const ServiceFormPage()));
    if (newService == true) {
      _serviceOptions = await repository.lookup('services');
      if (mounted) setState(() {});
    }
  }

  Future<void> _pickcustomerId() async {
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
                    children: [
                      Container(width: 42, height: 4, decoration: BoxDecoration(color: Theme.of(context).dividerColor, borderRadius: BorderRadius.circular(10))),
                      const SizedBox(height: 16),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text('Orarët e Lirë', style: TextStyle(fontSize: 20, fontWeight: FontWeight.w800)),
                          OutlinedButton.icon(
                            onPressed: () async {
                              final picked = await showDatePicker(
                                context: context,
                                initialDate: selectedDate,
                                firstDate: DateTime.now().subtract(const Duration(days: 30)),
                                lastDate: DateTime.now().add(const Duration(days: 180)),
                              );
                              if (picked != null) {
                                setSheetState(() => selectedDate = picked);
                              }
                            },
                            icon: const Icon(Icons.calendar_month_rounded, size: 16),
                            label: Text(_formatDisplayDate(selectedDate, includeTime: false), style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                          ),
                        ],
                      ),
                      const SizedBox(height: 14),
                      Expanded(
                        child: FutureBuilder<http.Response>(
                          future: ApiService.get('/bookings/day-schedule?date=$dateStr&barber_id=${_barberId ?? ''}&service_id=${_serviceId ?? ''}&ignore_booking_id=${widget.item?['id'] ?? ''}'),
                          builder: (context, snapshot) {
                            if (connectionStateLoading(snapshot)) {
                              return const Center(child: CircularProgressIndicator.adaptive());
                            }
                            if (!snapshot.hasData || snapshot.data?.statusCode != 200) {
                              return const Center(child: Text('Dështoi ngarkimi i orarëve.', style: TextStyle(color: Colors.grey)));
                            }

                            final body = jsonDecode(snapshot.data!.body);
                            final slots = (body['daySlots'] as List? ?? []).where((s) => s['is_free'] == true).toList();

                            if (slots.isEmpty) {
                              return Center(
                                child: Column(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: [
                                    const Icon(Icons.event_busy_rounded, size: 40, color: Colors.grey),
                                    const SizedBox(height: 10),
                                    Text(body['calendarMessage'] ?? 'Nuk ka orarë të lirë për këtë datë.', style: const TextStyle(color: Colors.grey, fontWeight: FontWeight.bold)),
                                  ],
                                ),
                              );
                            }

                            return GridView.builder(
                              gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                                crossAxisCount: 4,
                                childAspectRatio: 2.2,
                                crossAxisSpacing: 8,
                                mainAxisSpacing: 8,
                              ),
                              itemCount: slots.length,
                              itemBuilder: (context, index) {
                                final slot = slots[index];
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

  bool connectionStateLoading(AsyncSnapshot snapshot) {
    return snapshot.connectionState == ConnectionState.waiting;
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
    payload['service_id'] = _serviceId;
    payload['customer_id'] = _customerId;
    payload['appointment_at'] = _appointmentAtController.text.isEmpty ? null : _apiDateTimeValue(_appointmentAtController.text);
    payload['status'] = _status;
    payload['total_price'] = double.tryParse(_totalPriceController.text);
    payload['notes'] = _notesController.text;
    payload['source'] = _source;
    payload['send_sms'] = _sendSms;

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
    payload['send_sms'] = _sendSms;
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
    final isDark = theme.brightness == Brightness.dark;
    final primaryColor = theme.colorScheme.primary;

    final displayTimeStr = _appointmentAtController.text.isEmpty ? 'Sot, Në Pritje' : _appointmentAtController.text;
    final displayPriceStr = _totalPriceController.text.isEmpty ? '0.00 Lekë' : '${_totalPriceController.text} Lekë';
    final displayDurationStr = '${_totalSelectedDuration()} min';

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
              onPressed: () => Navigator.of(context).maybePop(),
            ),
            title: Text(widget.item?['id'] == null ? bookingTr(context, 'form.create_title') : bookingTr(context, 'form.edit_title'), style: const TextStyle(fontWeight: FontWeight.w800)),
          ),
          body: _loading ? const Center(child: CircularProgressIndicator.adaptive()) : Form(key: _formKey, child: Builder(builder: (formContext) => ListView(padding: const EdgeInsets.fromLTRB(20, 12, 20, 120), children: [
            // 🌟 SLEEK SUMMARY HEADER CARD
            Container(
              margin: const EdgeInsets.only(bottom: 18),
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: [
                    primaryColor.withOpacity(isDark ? 0.25 : 0.12),
                    primaryColor.withOpacity(isDark ? 0.10 : 0.04),
                  ],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(22),
                border: Border.all(color: primaryColor.withOpacity(0.3), width: 1.2),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Row(
                        children: [
                          Icon(Icons.event_available_rounded, size: 20, color: primaryColor),
                          const SizedBox(width: 8),
                          Text(
                            displayTimeStr,
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.w900,
                              color: isDark ? Colors.white : theme.colorScheme.onSurface,
                            ),
                          ),
                        ],
                      ),
                      InkWell(
                        onTap: _pickAvailableSlot,
                        borderRadius: BorderRadius.circular(10),
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                          decoration: BoxDecoration(
                            color: primaryColor,
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: const Row(
                            children: [
                              Icon(Icons.edit_calendar_rounded, size: 12, color: Colors.white),
                              SizedBox(width: 4),
                              Text('Ndrysho', style: TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold)),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: primaryColor.withOpacity(0.15),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          displayPriceStr,
                          style: TextStyle(fontSize: 13, fontWeight: FontWeight.w900, color: primaryColor),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        decoration: BoxDecoration(
                          color: theme.colorScheme.surfaceContainerHighest,
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Row(
                          children: [
                            Icon(Icons.access_time_rounded, size: 12, color: isDark ? Colors.white70 : Colors.black54),
                            const SizedBox(width: 4),
                            Text(
                              displayDurationStr,
                              style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.bold, color: isDark ? Colors.white70 : Colors.black87),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),

            if (AuthService.instance.user?['is_admin'] == true)
              _FieldShell(label: bookingTr(context, 'field.barber_shop_id'), child: InkWell(onTap: _pickbarberShopId, borderRadius: BorderRadius.circular(16), child: Container(padding: const EdgeInsets.all(16), decoration: BoxDecoration(border: Border.all(color: Theme.of(context).colorScheme.outlineVariant), borderRadius: BorderRadius.circular(16)), child: Row(children: [Expanded(child: Text(_displayName(_barberShopOptions.firstWhere((e) => e['id'].toString() == _barberShopId?.toString(), orElse: () => {'id': '', 'name': bookingTr(context, 'form.select')})))), const Icon(Icons.keyboard_arrow_down_rounded)])))),

            // 1. BARBER SELECTOR
            _FieldShell(label: bookingTr(context, 'field.barber_id'), child: InkWell(onTap: _pickbarberId, borderRadius: BorderRadius.circular(16), child: Container(padding: const EdgeInsets.all(16), decoration: BoxDecoration(border: Border.all(color: Theme.of(context).colorScheme.outlineVariant), borderRadius: BorderRadius.circular(16)), child: Row(children: [Expanded(child: Text(_displayName(_barberOptions.firstWhere((e) => e['id'].toString() == _barberId?.toString(), orElse: () => {'id': '', 'name': bookingTr(context, 'form.select')})), style: const TextStyle(fontWeight: FontWeight.bold))), const Icon(Icons.keyboard_arrow_down_rounded)])))),

            // 2. VISUAL SERVICE CHIPS SELECTOR (1-Tap Selection!)
            _FieldShell(
              label: '✂️ ZGJIDH SHËRBIMET (Kliko mbi shërbimin)',
              child: _serviceOptions.isEmpty
                  ? InkWell(
                      onTap: _pickserviceId,
                      child: Padding(
                        padding: const EdgeInsets.all(12),
                        child: Text(bookingTr(context, 'form.select'), style: const TextStyle(fontWeight: FontWeight.bold)),
                      ),
                    )
                  : Padding(
                      padding: const EdgeInsets.symmetric(vertical: 6),
                      child: Wrap(
                        spacing: 8,
                        runSpacing: 8,
                        children: _serviceOptions.map((service) {
                          final id = int.tryParse(service['id']?.toString() ?? '');
                          if (id == null) return const SizedBox.shrink();
                          final isSelected = _selectedServiceIds.contains(id);
                          final name = service['name']?.toString() ?? 'Shërbim';
                          final price = service['price'] != null ? '${service['price']}L' : '';
                          final duration = service['duration_minutes'] != null ? '${service['duration_minutes']}m' : '';

                          return InkWell(
                            onTap: () => _toggleServiceChip(id),
                            borderRadius: BorderRadius.circular(12),
                            child: AnimatedContainer(
                              duration: const Duration(milliseconds: 200),
                              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                              decoration: BoxDecoration(
                                color: isSelected
                                    ? primaryColor
                                    : (isDark ? const Color(0xFF262B38) : theme.colorScheme.surfaceContainerHighest.withOpacity(0.5)),
                                borderRadius: BorderRadius.circular(12),
                                border: Border.all(
                                  color: isSelected
                                      ? primaryColor
                                      : (isDark ? const Color(0xFF3B4052) : theme.colorScheme.outlineVariant),
                                  width: 1.2,
                                ),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Icon(
                                    isSelected ? Icons.check_circle_rounded : Icons.add_circle_outline_rounded,
                                    size: 16,
                                    color: isSelected ? Colors.white : (isDark ? Colors.grey.shade300 : Colors.black87),
                                  ),
                                  const SizedBox(width: 6),
                                  Text(
                                    name,
                                    style: TextStyle(
                                      fontSize: 12.5,
                                      fontWeight: FontWeight.bold,
                                      color: isSelected ? Colors.white : (isDark ? Colors.white : Colors.black87),
                                    ),
                                  ),
                                  if (price.isNotEmpty) ...[
                                    const SizedBox(width: 6),
                                    Text(
                                      '($price)',
                                      style: TextStyle(
                                        fontSize: 11,
                                        fontWeight: FontWeight.bold,
                                        color: isSelected ? Colors.white70 : primaryColor,
                                      ),
                                    ),
                                  ],
                                ],
                              ),
                            ),
                          );
                        }).toList(),
                      ),
                    ),
            ),

            // 3. CUSTOMER SELECTOR WITH QUICK INLINE ADD
            _FieldShell(
              label: bookingTr(context, 'field.customer_id'),
              child: Row(
                children: [
                  Expanded(
                    child: InkWell(
                      onTap: _pickcustomerId,
                      borderRadius: BorderRadius.circular(16),
                      child: Container(
                        padding: const EdgeInsets.all(14),
                        decoration: BoxDecoration(
                          border: Border.all(color: Theme.of(context).colorScheme.outlineVariant),
                          borderRadius: BorderRadius.circular(16),
                        ),
                        child: Row(
                          children: [
                            Expanded(
                              child: Text(
                                _displayName(_customerOptions.firstWhere((e) => e['id'].toString() == _customerId?.toString(), orElse: () => {'id': '', 'name': bookingTr(context, 'form.select')})),
                                style: const TextStyle(fontWeight: FontWeight.bold),
                              ),
                            ),
                            const Icon(Icons.keyboard_arrow_down_rounded),
                          ],
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  IconButton.filledTonal(
                    tooltip: 'Shto Klient të Ri',
                    icon: const Icon(Icons.person_add_alt_1_rounded, size: 20),
                    onPressed: () async {
                      final newCust = await Navigator.push<bool>(context, MaterialPageRoute(builder: (_) => const CustomerFormPage()));
                      if (newCust == true) {
                        _customerOptions = await repository.lookup('customers');
                        if (mounted) setState(() {});
                      }
                    },
                  ),
                ],
              ),
            ),

            _FieldShell(
              label: bookingTr(context, 'field.appointment_at'),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  InkWell(
                    onTap: _pickAvailableSlot,
                    borderRadius: BorderRadius.circular(12),
                    child: InputDecorator(
                      decoration: const InputDecoration(border: InputBorder.none, isDense: true, suffixIcon: Icon(Icons.event_available_rounded)),
                      child: Text(
                        _appointmentAtController.text.isEmpty ? bookingTr(context, 'form.select_datetime') : _appointmentAtController.text,
                        style: TextStyle(color: _appointmentAtController.text.isEmpty ? Theme.of(context).colorScheme.onSurfaceVariant : null, fontWeight: FontWeight.bold, fontSize: 15),
                      ),
                    ),
                  ),
                  if (_slotWarning != null) ...[
                    const SizedBox(height: 6),
                    InkWell(
                      onTap: _pickAvailableSlot,
                      borderRadius: BorderRadius.circular(12),
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                        decoration: BoxDecoration(color: Colors.red.withOpacity(0.12), borderRadius: BorderRadius.circular(10), border: Border.all(color: Colors.red.withOpacity(0.5))),
                        child: Row(
                          children: [
                            const Icon(Icons.warning_amber_rounded, color: Colors.red, size: 16),
                            const SizedBox(width: 6),
                            Expanded(child: Text(_slotWarning!, style: const TextStyle(color: Colors.red, fontSize: 11.5, fontWeight: FontWeight.bold))),
                          ],
                        ),
                      ),
                    ),
                  ],
                ],
              ),
            ),

            _FieldShell(label: bookingTr(context, 'field.status'), child: DropdownButtonFormField<String>(value: ['pending', 'confirmed', 'completed', 'cancelled', 'no-show'].contains(_status) ? _status : null, items: ['pending', 'confirmed', 'completed', 'cancelled', 'no-show'].map((v) => DropdownMenuItem<String>(value: v, child: Text(v))).toList(), onChanged: (v) => setState(() => _status = v), validator: (v) { if (v == null || v.isEmpty) return bookingTr(context, 'form.select'); return null; }, decoration: const InputDecoration(border: InputBorder.none, isDense: true))),
            _FieldShell(label: bookingTr(context, 'field.total_price'), child: TextFormField(controller: _totalPriceController, keyboardType: const TextInputType.numberWithOptions(decimal: true), inputFormatters: [TextInputFormatter.withFunction((oldValue, newValue) { final text = newValue.text; if (text.isEmpty || RegExp(r'^\d*\.?\d{0,2}$').hasMatch(text)) return newValue; return oldValue; })],  decoration: InputDecoration(hintText: bookingTr(context, 'field.total_price'), border: InputBorder.none, isDense: true), validator: (v) { if (v == null || v.trim().isEmpty) return bookingTr(context, 'form.required'); if (v != null && v.isNotEmpty && (double.tryParse(v) == null || !RegExp(r'^\d+(\.\d{1,2})?$').hasMatch(v))) return 'Enter a valid number with up to 2 decimals';  return null; })),
            _FieldShell(label: bookingTr(context, 'field.notes'), child: TextFormField(controller: _notesController,  decoration: InputDecoration(hintText: bookingTr(context, 'field.notes'), border: InputBorder.none, isDense: true), validator: (v) {  return null; })),
            _FieldShell(label: bookingTr(context, 'field.source'), child: DropdownButtonFormField<String>(value: ['online', 'walk-in', 'phone'].contains(_source) ? _source : null, items: ['online', 'walk-in', 'phone'].map((v) => DropdownMenuItem<String>(value: v, child: Text(v))).toList(), onChanged: (v) => setState(() => _source = v), validator: (v) { if (v == null || v.isEmpty) return bookingTr(context, 'form.select'); return null; }, decoration: const InputDecoration(border: InputBorder.none, isDense: true))),

            // Toggle for SMS Notification
            Container(
              margin: const EdgeInsets.only(bottom: 14),
              decoration: BoxDecoration(
                color: isDark ? const Color(0xFF1E212B) : theme.colorScheme.surfaceContainerHighest.withOpacity(.5),
                border: Border.all(
                  color: isDark ? const Color(0xFF3B4052) : theme.colorScheme.outlineVariant,
                  width: 1.2,
                ),
                borderRadius: BorderRadius.circular(18),
              ),
              child: SwitchListTile.adaptive(
                contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 2),
                title: Row(
                  children: [
                    Icon(Icons.sms_rounded, size: 18, color: theme.colorScheme.primary),
                    const SizedBox(width: 8),
                    const Text(
                      'Dërgo SMS Njoftimi',
                      style: TextStyle(fontSize: 13.5, fontWeight: FontWeight.bold),
                    ),
                  ],
                ),
                subtitle: Text(
                  _sendSms ? 'Automatike: Dërgohet SMS konfirmimi & rikujtesa' : 'Mos dërgo SMS njoftimi për këtë rezervim',
                  style: TextStyle(fontSize: 11, color: _sendSms ? Colors.green.shade700 : Colors.grey),
                ),
                value: _sendSms,
                onChanged: (v) => setState(() => _sendSms = v),
              ),
            ),

            // 🌟 DETAILED SMS MESSAGES STATUS SECTION FOR THIS BOOKING
            if (widget.item?['sms_messages'] != null && (widget.item!['sms_messages'] as List).isNotEmpty) ...[
              _FieldShell(
                label: '📱 STATUSI I MESAZHEVE SMS',
                child: Column(
                  children: (widget.item!['sms_messages'] as List).map((msg) {
                    final typeLabel = (msg['type_label'] ?? 'SMS').toString();
                    final status = (msg['status'] ?? 'pending').toString().toLowerCase();
                    final content = (msg['message_content'] ?? '').toString();
                    final scheduledAt = msg['scheduled_at']?.toString();
                    final sentAt = msg['updated_at']?.toString();

                    Color color = Colors.amber.shade700;
                    if (status == 'sent') color = Colors.green.shade600;
                    if (status == 'failed') color = Colors.red.shade600;

                    return Container(
                      margin: const EdgeInsets.only(top: 6, bottom: 4),
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: color.withOpacity(0.08),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: color.withOpacity(0.3)),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text(typeLabel, style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: color)),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                decoration: BoxDecoration(color: color.withOpacity(0.15), borderRadius: BorderRadius.circular(6)),
                                child: Text(status.toUpperCase(), style: TextStyle(fontSize: 9.5, fontWeight: FontWeight.bold, color: color)),
                              ),
                            ],
                          ),
                          const SizedBox(height: 4),
                          Text(content, style: TextStyle(fontSize: 11.5, height: 1.3, color: isDark ? Colors.white70 : Colors.black87)),
                          const SizedBox(height: 6),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              if (scheduledAt != null)
                                Text('Planifikuar: ${_formatDisplayDate(DateTime.tryParse(scheduledAt) ?? DateTime.now(), includeTime: true)}', style: const TextStyle(fontSize: 10, color: Colors.grey)),
                              if (status == 'sent' && sentAt != null)
                                Text('Dërguar: ${_formatDisplayDate(DateTime.tryParse(sentAt) ?? DateTime.now(), includeTime: true)}', style: TextStyle(fontSize: 10, color: Colors.green.shade700, fontWeight: FontWeight.bold)),
                            ],
                          ),
                        ],
                      ),
                    );
                  }).toList(),
                ),
              ),
            ],

            const SizedBox(height: 14),
            BlocBuilder<BookingCubit, BookingState>(
              builder: (context, state) => PremiumButton(
                onPressed: () => _save(context),
                label: state is BookingSaving ? bookingTr(context, 'form.saving') : 'Krijo Rezervimin ($displayPriceStr)',
                icon: Icons.check_circle_rounded,
                loading: state is BookingSaving,
                expand: true,
              ),
            ),
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
          Text(label, style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: Theme.of(context).colorScheme.onSurfaceVariant)),
          child,
        ],
      ),
    );
  }
}
