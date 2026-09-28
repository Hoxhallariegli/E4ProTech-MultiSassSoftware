import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:mobile_gateway/core/widgets/app_scaffold.dart';
import 'package:mobile_gateway/core/widgets/premium_header.dart';
import 'package:mobile_gateway/services/api_service.dart';
import 'package:mobile_gateway/services/auth_service.dart';
import 'package:mobile_gateway/modules/dashboard/presentation/widgets/shop_switcher_widget.dart';
import 'package:mobile_gateway/modules/dashboard/payment/presentation/pages/payment_form_page.dart';
import 'package:mobile_gateway/core/branding/branding_cubit.dart';
import 'package:mobile_gateway/l10n/booking_localization.dart';
import '../cubit/booking_cubit.dart';
import '../cubit/booking_state.dart';
import '../../data/booking_repository.dart';
import 'booking_form_page.dart';

class BookingCalendarPage extends StatefulWidget {
  const BookingCalendarPage({super.key});
  @override State<BookingCalendarPage> createState() => BookingCalendarPageState();
}

class BookingCalendarPageState extends State<BookingCalendarPage> {
  DateTime _focusedDay = DateTime.now();
  DateTime _selectedDay = DateTime(DateTime.now().year, DateTime.now().month, DateTime.now().day);
  int? _selectedBarberId;
  final ScrollController _scrollController = ScrollController();

  @override
  void initState() {
    super.initState();
    AuthService.instance.addListener(_onAuthChanged);
    refreshAll(reloadBarbers: true);
  }

  @override
  void dispose() {
    AuthService.instance.removeListener(_onAuthChanged);
    _scrollController.dispose();
    super.dispose();
  }

  void _onAuthChanged() {
    if (mounted) {
      refreshAll(reloadBarbers: true);
    }
  }

  void _scrollToCurrentTime(List<dynamic> displaySlots) {
    if (displaySlots.isEmpty) return;
    final now = DateTime.now();
    final today = DateTime(now.year, now.month, now.day);
    final isSelectedToday = _selectedDay.year == today.year && _selectedDay.month == today.month && _selectedDay.day == today.day;
    if (!isSelectedToday) return;

    int targetIndex = 0;
    int minDiff = 999999;

    for (int i = 0; i < displaySlots.length; i++) {
      final slot = displaySlots[i];
      final time = (slot['time'] ?? '00:00').toString();
      final parts = time.split(':');
      final slotTime = DateTime(
        _selectedDay.year,
        _selectedDay.month,
        _selectedDay.day,
        int.tryParse(parts[0]) ?? 0,
        int.tryParse(parts.length > 1 ? parts[1] : '0') ?? 0,
      );
      final diff = slotTime.difference(now).inMinutes;
      if (diff >= 0 && diff < minDiff) {
        minDiff = diff;
        targetIndex = i;
        break;
      }
    }

    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!mounted) return;
      if (_scrollController.hasClients && _scrollController.position.hasContentDimensions) {
        try {
          final position = _scrollController.position;
          final itemOffset = targetIndex * 75.0;
          final viewportHalf = (position.hasViewportDimension && position.viewportDimension > 0)
              ? position.viewportDimension / 2
              : 200.0;
          final maxExtent = position.hasContentDimensions ? position.maxScrollExtent : 0.0;
          final centeredOffset = (itemOffset - viewportHalf + 37.5).clamp(
            0.0,
            maxExtent,
          );
          if (centeredOffset >= 0) {
            _scrollController.animateTo(
              centeredOffset,
              duration: const Duration(milliseconds: 600),
              curve: Curves.easeInOut,
            );
          }
        } catch (_) {}
      }
    });
  }

  Future<void> refreshAll({bool reloadBarbers = true}) async {
    if (!mounted) return;
    context.read<BookingCubit>().refreshCalendarData(
      month: _focusedDay,
      day: _selectedDay,
      preferredBarberId: reloadBarbers ? null : _selectedBarberId,
      reloadBarbersList: reloadBarbers,
    );
  }

  void _refresh() {
    refreshAll(reloadBarbers: false);
  }

  String _formatDate(DateTime day) {
    return '${day.year.toString().padLeft(4, '0')}-'
        '${day.month.toString().padLeft(2, '0')}-'
        '${day.day.toString().padLeft(2, '0')}';
  }

  void _loadSelectedDay(DateTime day) {
    if (!mounted) return;

    final cleanDay = DateTime(day.year, day.month, day.day);

    setState(() {
      _selectedDay = cleanDay;
      _focusedDay = cleanDay;
    });

    context.read<BookingCubit>().refreshCalendarData(
      month: cleanDay,
      day: cleanDay,
      preferredBarberId: _selectedBarberId,
      reloadBarbersList: false,
    );
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Scaffold(
      backgroundColor: theme.colorScheme.surface,
      body: BlocBuilder<BookingCubit, BookingState>(
        builder: (context, state) {
          final isCalendarLoaded = state is BookingCalendarLoaded;
          final barbers = isCalendarLoaded ? state.barbers : const <Map<String, dynamic>>[];
          final calendarStats = isCalendarLoaded ? state.calendarStats : const <String, int>{};
          final daySlots = isCalendarLoaded ? state.daySlots : const [];
          final isLoading = isCalendarLoaded ? state.isLoading : true;
          final calendarMessage = isCalendarLoaded ? state.calendarMessage : null;

          if (isCalendarLoaded && state.selectedBarberId != null) {
            _selectedBarberId = state.selectedBarberId;
          }

          return Column(
            children: [
              _buildBarberFilter(barbers),
              const SizedBox(height: 8),
              _buildCustomWeekCalendar(calendarStats),
              const SizedBox(height: 4),
              Expanded(
                child: isLoading
                    ? const Center(child: CircularProgressIndicator.adaptive())
                    : _buildFilteredTimeline(daySlots, calendarMessage),
              ),
            ],
          );
        },
      ),
      floatingActionButton: FloatingActionButton(
        backgroundColor: theme.colorScheme.primary,
        onPressed: () async {
          final res = await Navigator.push(
            context,
            MaterialPageRoute(builder: (c) => const BookingFormPage()),
          );
          if (res == true) _refresh();
        },
        child: const Icon(Icons.add, color: Colors.white, size: 28),
      ),
    );
  }

  Widget _buildBarberFilter(List<Map<String, dynamic>> barbers) {
    if (barbers.isEmpty) return const SizedBox.shrink();

    return Container(
      height: 60,
      child: ListView(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
        children: barbers.map((b) => _barberChip((b['name'] ?? context.staffLabel).toString(), int.tryParse(b['id']?.toString() ?? ''))).toList(),
      ),
    );
  }

  Widget _barberChip(String label, int? id) {
    final theme = Theme.of(context);
    final isSelected = _selectedBarberId == id;
    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: ChoiceChip(
        label: Text(label),
        selected: isSelected,
        selectedColor: theme.colorScheme.primary,
        backgroundColor: theme.colorScheme.surfaceContainerHighest,
        labelStyle: TextStyle(
          color: isSelected ? Colors.white : theme.colorScheme.onSurface,
          fontSize: 12,
          fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
        ),
        onSelected: (_) {
          setState(() => _selectedBarberId = id);
          _refresh();
        },
      ),
    );
  }

  Widget _buildCustomWeekCalendar(Map<String, int> stats) {
    final theme = Theme.of(context);
    final startDay = DateTime.now().subtract(const Duration(days: 4));

    return Container(
      height: 84,
      padding: const EdgeInsets.symmetric(vertical: 8),
      decoration: BoxDecoration(
        color: theme.colorScheme.surface,
        border: Border(bottom: BorderSide(color: theme.dividerColor, width: 0.5)),
      ),
      child: ListView.builder(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 12),
        itemCount: 14,
        itemBuilder: (context, index) {
          final day = startDay.add(Duration(days: index));
          final isSelected = day.year == _selectedDay.year && day.month == _selectedDay.month && day.day == _selectedDay.day;
          final dateStr = "${day.year}-${day.month.toString().padLeft(2, '0')}-${day.day.toString().padLeft(2, '0')}";
          final hasBookings = (stats[dateStr] ?? 0) > 0;

          final isEn = Localizations.localeOf(context).languageCode == 'en';
          final weekDays = isEn
              ? ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
              : ['Dje', 'Hën', 'Mar', 'Mër', 'Enj', 'Pre', 'Sht'];
          final dayName = weekDays[day.weekday % 7];

          return GestureDetector(
            onTap: () => _loadSelectedDay(day),
            child: Container(
              width: 54,
              margin: const EdgeInsets.symmetric(horizontal: 4),
              decoration: BoxDecoration(
                color: isSelected ? theme.colorScheme.primary : theme.colorScheme.surfaceContainerHighest.withOpacity(0.3),
                borderRadius: BorderRadius.circular(16),
                border: Border.all(
                  color: isSelected ? theme.colorScheme.primary : theme.colorScheme.outlineVariant,
                  width: 1,
                ),
              ),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Text(
                    dayName,
                    style: TextStyle(
                      fontSize: 11,
                      color: isSelected ? Colors.white : Colors.grey,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    day.day.toString(),
                    style: TextStyle(
                      fontSize: 15,
                      color: isSelected ? Colors.white : theme.colorScheme.onSurface,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  if (hasBookings) ...[
                    const SizedBox(height: 4),
                    Container(
                      width: 5,
                      height: 5,
                      decoration: BoxDecoration(
                        color: isSelected ? Colors.white : theme.colorScheme.primary,
                        shape: BoxShape.circle,
                      ),
                    ),
                  ],
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _buildFilteredTimeline(List<dynamic> slots, String? message) {
    final theme = Theme.of(context);

    if (_selectedBarberId == null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Text(
            'Zgjidhni një ${context.staffLabel.toLowerCase()} për të parë axhendën.',
            style: const TextStyle(color: Colors.grey, fontWeight: FontWeight.bold),
          ),
        ),
      );
    }

    if (slots.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.event_busy_rounded, size: 48, color: theme.colorScheme.outlineVariant),
            const SizedBox(height: 12),
            Text(
              message ?? 'Nuk ka të dhëna për këtë ditë.',
              style: const TextStyle(color: Colors.grey, fontWeight: FontWeight.bold),
            ),
          ],
        ),
      );
    }

    final now = DateTime.now();
    final today = DateTime(now.year, now.month, now.day);
    final isSelectedToday = _selectedDay.year == today.year && _selectedDay.month == today.month && _selectedDay.day == today.day;
    final isSelectedPast = _selectedDay.isBefore(today);

    // Nuk ka më tab/filter: shfaqim gjithë axhendën e ditës nga fillimi deri
    // në mbyllje, pa e ndërprerë listën te ora aktuale.
    final displaySlots = slots;
    _scrollToCurrentTime(displaySlots);

    return ListView.builder(
      controller: _scrollController,
      padding: const EdgeInsets.all(16),
      itemCount: displaySlots.length,
      itemBuilder: (context, index) {
          final slot = displaySlots[index];
          final time = (slot['time'] ?? '00:00').toString();
          final isFree = slot['is_free'] == true;

          final parts = time.split(':');
          final slotTime = DateTime(
            _selectedDay.year,
            _selectedDay.month,
            _selectedDay.day,
            int.tryParse(parts[0]) ?? 0,
            int.tryParse(parts.length > 1 ? parts[1] : '0') ?? 0,
          );
          final isPast = isSelectedPast || (isSelectedToday && slotTime.isBefore(now));

          return Padding(
            padding: const EdgeInsets.symmetric(vertical: 6),
            child: Opacity(
              opacity: isPast ? 0.55 : 1.0,
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  SizedBox(
                    width: 48,
                    child: Padding(
                      padding: const EdgeInsets.only(top: 14),
                      child: Text(
                        time,
                        style: TextStyle(
                          fontWeight: FontWeight.bold,
                          color: isPast ? Colors.grey.shade400 : Colors.grey,
                          fontSize: 13,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: isFree
                        ? _buildFreeSlot(time, isPast)
                        : (slot['booking'] != null && (slot['booking'] as Map).isNotEmpty
                            ? ((slot['booking']['status'] ?? '').toString() == 'break'
                                ? _buildBreakCard(slot['booking'])
                                : _buildBookingCard(slot['booking']))
                            : _buildUnavailableSlot(time, slot['message'])),
                  ),
                ],
              ),
            ),
          );
        },
      );
  }

  Widget _buildUnavailableSlot(String time, String? message) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF1E212B).withOpacity(0.5) : Colors.grey.shade100,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: Colors.grey.withOpacity(0.3),
          width: 1,
        ),
      ),
      child: Row(
        children: [
          const Icon(Icons.block_rounded, color: Colors.grey, size: 18),
          const SizedBox(width: 8),
          Text(
            message ?? bookingTr(context, 'calendar.unavailable'),
            style: const TextStyle(fontWeight: FontWeight.w600, color: Colors.grey, fontSize: 12),
          ),
        ],
      ),
    );
  }

  Widget _buildFreeSlot(String time, bool isPast) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;

    return InkWell(
      onTap: () async {
        final dateStr = _formatDate(_selectedDay);
        final appointmentAt = "$dateStr $time:00";

        final res = await Navigator.push(
          context,
          MaterialPageRoute(
            builder: (c) => BookingFormPage(
              item: {
                'appointment_at': appointmentAt,
                'barber_id': _selectedBarberId,
                'source': isPast ? 'walk-in' : 'online',
                'status': isPast ? 'completed' : 'pending',
              },
            ),
          ),
        );
        if (res == true) _refresh();
      },
      borderRadius: BorderRadius.circular(12),
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: isPast
              ? (isDark ? const Color(0xFF15171F) : Colors.grey.shade100)
              : (isDark ? const Color(0xFF1B1E29) : theme.colorScheme.surfaceContainerHighest.withOpacity(0.3)),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: isPast
                ? Colors.grey.withOpacity(0.3)
                : (isDark ? theme.colorScheme.primary.withOpacity(0.4) : theme.colorScheme.outlineVariant),
            width: 1,
          ),
        ),
        child: Row(
          children: [
            Icon(
              isPast ? Icons.history_rounded : Icons.add_circle_outline_rounded,
              color: isPast ? Colors.grey : (isDark ? const Color(0xFF60A5FA) : theme.colorScheme.primary),
              size: 18,
            ),
            const SizedBox(width: 8),
            Text(
              isPast ? bookingTr(context, 'calendar.add_walk_in', {'time': time}) : bookingTr(context, 'calendar.book_at', {'time': time}),
              style: TextStyle(
                fontWeight: FontWeight.w700,
                color: isPast ? Colors.grey.shade600 : (isDark ? const Color(0xFF60A5FA) : theme.colorScheme.primary),
                fontSize: 13,
              ),
            ),
            const Spacer(),
            const Icon(Icons.arrow_forward_ios_rounded, size: 12, color: Colors.grey),
          ],
        ),
      ),
    );
  }

  Widget _buildBreakCard(Map<String, dynamic> breakData) {
    final theme = Theme.of(context);
    final durationMinutes =
        int.tryParse(breakData['duration_minutes']?.toString() ?? '30') ?? 30;
    final appointmentAtStr = breakData['appointment_at']?.toString();

    String timeRangeText = '';
    if (appointmentAtStr != null && appointmentAtStr.isNotEmpty) {
      final clean = appointmentAtStr.replaceAll('T', ' ');
      final parts = clean.split(' ');
      if (parts.length >= 2) {
        final timeParts = parts[1].split(':');
        if (timeParts.length >= 2) {
          final startHour = int.tryParse(timeParts[0]) ?? 0;
          final startMinute = int.tryParse(timeParts[1]) ?? 0;
          final startDt = DateTime(_selectedDay.year, _selectedDay.month, _selectedDay.day, startHour, startMinute);
          final endDt = startDt.add(Duration(minutes: durationMinutes));
          final startFormatted = "${startDt.hour.toString().padLeft(2, '0')}:${startDt.minute.toString().padLeft(2, '0')}";
          final endFormatted = "${endDt.hour.toString().padLeft(2, '0')}:${endDt.minute.toString().padLeft(2, '0')}";
          timeRangeText = "$startFormatted - $endFormatted";
        }
      }
    }

    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.orange.withOpacity(0.10),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: Colors.orange.withOpacity(0.45),
          width: 1.2,
        ),
      ),
      child: Row(
        children: [
          Container(
            width: 42,
            height: 42,
            decoration: BoxDecoration(
              color: Colors.orange.withOpacity(0.15),
              shape: BoxShape.circle,
            ),
            child: const Icon(
              Icons.free_breakfast_rounded,
              color: Colors.orange,
              size: 22,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'PUSHIM',
                  style: TextStyle(
                    color: Colors.orange,
                    fontWeight: FontWeight.w900,
                    fontSize: 14,
                  ),
                ),
                const SizedBox(height: 3),
                Text(
                  timeRangeText.isNotEmpty ? timeRangeText : 'Pushim i orarit',
                  style: TextStyle(
                    color: theme.colorScheme.onSurface,
                    fontWeight: FontWeight.bold,
                    fontSize: 13,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  'Orar Pushimi / Dreka',
                  style: TextStyle(
                    color: theme.colorScheme.onSurfaceVariant,
                    fontSize: 11,
                  ),
                ),
              ],
            ),
          ),
          const Icon(
            Icons.restaurant_rounded,
            color: Colors.orange,
            size: 20,
          ),
        ],
      ),
    );
  }

  Widget _buildBookingCard(Map<String, dynamic> booking) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    final customer = (booking['customer_name'] ?? booking['customer']?['name'] ?? 'Klient').toString();
    final service = (booking['service_name'] ?? booking['service']?['name'] ?? 'Shërbim').toString();
    final barber = (booking['barber_name'] ?? booking['barber']?['name'] ?? context.staffLabel).toString();
    final status = (booking['status'] ?? 'pending').toString();
    final totalPrice = booking['total_price']?.toString();
    final durationMinutes = booking['duration_minutes']?.toString();
    final appointmentAtStr = booking['appointment_at']?.toString();

    final durationMin = int.tryParse(durationMinutes ?? '30') ?? 30;
    String timeRangeText = '';
    if (appointmentAtStr != null && appointmentAtStr.isNotEmpty) {
      final clean = appointmentAtStr.replaceAll('T', ' ');
      final parts = clean.split(' ');
      if (parts.length >= 2) {
        final timeParts = parts[1].split(':');
        if (timeParts.length >= 2) {
          final startHour = int.tryParse(timeParts[0]) ?? 0;
          final startMinute = int.tryParse(timeParts[1]) ?? 0;
          final startDt = DateTime(_selectedDay.year, _selectedDay.month, _selectedDay.day, startHour, startMinute);
          final endDt = startDt.add(Duration(minutes: durationMin));
          final startFormatted = "${startDt.hour.toString().padLeft(2, '0')}:${startDt.minute.toString().padLeft(2, '0')}";
          final endFormatted = "${endDt.hour.toString().padLeft(2, '0')}:${endDt.minute.toString().padLeft(2, '0')}";
          timeRangeText = "$startFormatted - $endFormatted";
        }
      }
    }

    Color statusColor = Colors.orange;
    if (status == 'completed') statusColor = Colors.green;
    if (status == 'confirmed') statusColor = Colors.blue;
    if (status == 'cancelled') statusColor = Colors.red;
    if (status == 'no-show') statusColor = Colors.purple;

    final isPendingOrConfirmed = status == 'pending' || status == 'confirmed';

    return InkWell(
      onTap: () => _showBookingActionSheet(booking),
      borderRadius: BorderRadius.circular(16),
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: isDark ? const Color(0xFF1E212B) : theme.colorScheme.surface,
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(isDark ? 0.25 : 0.04),
              blurRadius: 8,
              offset: const Offset(0, 4),
            )
          ],
          border: Border.all(
            color: isDark ? const Color(0xFF3B4052) : theme.colorScheme.outlineVariant,
            width: 1.2,
          ),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        customer,
                        style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 15),
                      ),
                      if (timeRangeText.isNotEmpty) ...[
                        const SizedBox(height: 3),
                        Row(
                          children: [
                            Icon(Icons.access_time_rounded, size: 12, color: theme.colorScheme.primary),
                            const SizedBox(width: 4),
                            Text(
                              timeRangeText,
                              style: TextStyle(color: theme.colorScheme.primary, fontWeight: FontWeight.bold, fontSize: 12),
                            ),
                          ],
                        ),
                      ],
                    ],
                  ),
                ),
                Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    if (booking['payment_status'] != null && booking['payment_status'].toString().isNotEmpty && booking['payment_status'].toString() != 'unpaid') ...[
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 3),
                        margin: const EdgeInsets.only(right: 6),
                        decoration: BoxDecoration(
                          color: _paymentStatusColor(booking['payment_status'].toString()).withOpacity(0.15),
                          borderRadius: BorderRadius.circular(6),
                          border: Border.all(color: _paymentStatusColor(booking['payment_status'].toString()).withOpacity(0.5), width: 0.8),
                        ),
                        child: Row(
                          children: [
                            Icon(_paymentStatusIcon(booking['payment_status'].toString()), size: 10, color: _paymentStatusColor(booking['payment_status'].toString())),
                            const SizedBox(width: 3),
                            Text(booking['payment_status'].toString().toUpperCase(), style: TextStyle(color: _paymentStatusColor(booking['payment_status'].toString()), fontWeight: FontWeight.bold, fontSize: 9)),
                          ],
                        ),
                      ),
                    ],
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: statusColor.withOpacity(0.12),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        status.toUpperCase(),
                        style: TextStyle(color: statusColor, fontWeight: FontWeight.bold, fontSize: 9.5),
                      ),
                    ),
                  ],
                ),
              ],
            ),
            const SizedBox(height: 6),
            Row(
              children: [
                Expanded(
                  child: Row(
                    children: [
                      Expanded(
                        child: Text(
                          service,
                          style: TextStyle(color: theme.colorScheme.primary, fontWeight: FontWeight.bold, fontSize: 13),
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      if (durationMinutes != null && durationMinutes.isNotEmpty) ...[
                        const SizedBox(width: 6),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 2),
                          decoration: BoxDecoration(
                            color: theme.colorScheme.primary.withOpacity(0.1),
                            borderRadius: BorderRadius.circular(4),
                          ),
                          child: Text(
                            '${durationMinutes}m',
                            style: TextStyle(color: theme.colorScheme.primary, fontSize: 10, fontWeight: FontWeight.bold),
                          ),
                        ),
                      ],
                    ],
                  ),
                ),
                if (totalPrice != null && totalPrice.isNotEmpty) ...[
                  const SizedBox(width: 8),
                  Text(
                    '($totalPrice Lekë)',
                    style: TextStyle(color: theme.colorScheme.onSurfaceVariant, fontSize: 11, fontWeight: FontWeight.w700),
                  ),
                ],
              ],
            ),
            const SizedBox(height: 4),
            Row(
              children: [
                const Icon(Icons.person_outline_rounded, size: 13, color: Colors.grey),
                const SizedBox(width: 4),
                Text(
                  barber,
                  style: const TextStyle(color: Colors.grey, fontSize: 12),
                ),
              ],
            ),
            if (isPendingOrConfirmed && booking['payment_status'] != 'paid') ...[
              const SizedBox(height: 10),
              const Divider(height: 1, thickness: 0.5),
              const SizedBox(height: 8),
              Row(
                mainAxisAlignment: MainAxisAlignment.end,
                children: [
                  OutlinedButton.icon(
                    onPressed: () => _completeAndPay(booking),
                    icon: const Icon(Icons.check_circle_outline_rounded, size: 16, color: Colors.green),
                    label: const Text('Kryej & Regjistro Pagesën', style: TextStyle(color: Colors.green, fontWeight: FontWeight.bold, fontSize: 11.5)),
                    style: OutlinedButton.styleFrom(
                      side: const BorderSide(color: Colors.green),
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    ),
                  ),
                ],
              ),
            ],
          ],
        ),
      ),
    );
  }

  Future<void> _showBookingActionSheet(Map<String, dynamic> booking) async {
    final bookingId = booking['id'];
    final customer = (booking['customer_name'] ?? booking['customer']?['name'] ?? 'Klient').toString();
    final service = (booking['service_name'] ?? booking['service']?['name'] ?? 'Shërbim').toString();
    final status = (booking['status'] ?? '').toString();
    final paymentStatus = (booking['payment_status'] ?? '').toString();
    final bool isPaid = paymentStatus == 'paid';
    final bool isCompleted = status == 'completed';
    final bool isClosed = isPaid || isCompleted;

    await showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      useSafeArea: true,
      backgroundColor: Theme.of(context).colorScheme.surface,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(28))),
      builder: (sheetContext) => SafeArea(
        child: Padding(
          padding: EdgeInsets.fromLTRB(
            20,
            20,
            20,
            20 + MediaQuery.of(sheetContext).padding.bottom,
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(width: 40, height: 4, decoration: BoxDecoration(color: Colors.grey.shade300, borderRadius: BorderRadius.circular(10))),
              ),
              const SizedBox(height: 16),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Expanded(child: Text(customer, style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w900))),
                  if (paymentStatus.isNotEmpty && paymentStatus != 'unpaid')
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: _paymentStatusColor(paymentStatus).withOpacity(0.15),
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(_paymentStatusIcon(paymentStatus), color: _paymentStatusColor(paymentStatus), size: 14),
                          const SizedBox(width: 4),
                          Text(paymentStatus.toUpperCase(), style: TextStyle(color: _paymentStatusColor(paymentStatus), fontWeight: FontWeight.w900, fontSize: 11)),
                        ],
                      ),
                    ),
                ],
              ),
              const SizedBox(height: 4),
              Text(service, style: TextStyle(fontSize: 13, color: Theme.of(context).colorScheme.primary, fontWeight: FontWeight.bold)),
              const SizedBox(height: 20),

              if (!isPaid) ...[
                ListTile(
                  leading: const Icon(Icons.check_circle_rounded, color: Colors.green, size: 26),
                  title: const Text('Kryej Takimin & Regjistro Pagesën', style: TextStyle(fontWeight: FontWeight.bold)),
                  subtitle: const Text('Ndryshon statusin në completed dhe regjistron arketimin'),
                  onTap: () {
                    Navigator.pop(sheetContext);
                    _completeAndPay(booking);
                  },
                ),
                const Divider(height: 1),
              ],

              ListTile(
                leading: const Icon(Icons.edit_note_rounded, color: Colors.blueAccent, size: 26),
                title: const Text('Ndrysho / Shto Shërbim Shtesë', style: TextStyle(fontWeight: FontWeight.bold)),
                subtitle: const Text('Modifiko shërbimin, çmimin ose oren e takimit'),
                onTap: () async {
                  Navigator.pop(sheetContext);
                  final res = await Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => BookingFormPage(item: booking)),
                  );
                  if (res == true) _refresh();
                },
              ),

              if (!isClosed) ...[
                const Divider(height: 1),
                ListTile(
                  leading: const Icon(Icons.person_off_rounded, color: Colors.purple, size: 26),
                  title: const Text('Klienti Nuk Erdhi (No-Show)', style: TextStyle(fontWeight: FontWeight.bold)),
                  subtitle: const Text('Shënon takimin si mosardhje të klientit'),
                  onTap: () async {
                    Navigator.pop(sheetContext);
                    await _updateBookingStatus(bookingId, 'no-show');
                  },
                ),
                const Divider(height: 1),
                ListTile(
                  leading: const Icon(Icons.cancel_rounded, color: Colors.red, size: 26),
                  title: const Text('Anulo Rezervimin', style: TextStyle(fontWeight: FontWeight.bold, color: Colors.red)),
                  onTap: () async {
                    Navigator.pop(sheetContext);
                    await _updateBadgeAndRefreshStatus(bookingId, 'cancelled');
                  },
                ),
              ],
              const SizedBox(height: 10),
            ],
          ),
        ),
      ),
    );
  }

  Future<void> _updateBadgeAndRefreshStatus(dynamic bookingId, String status) async {
    await _updateBookingStatus(bookingId, status);
  }

  Future<void> _completeAndPay(Map<String, dynamic> booking) async {
    final bookingId = booking['id'];
    try {
      if (bookingId != null) {
        await ApiService.put('/bookings/$bookingId', {'status': 'completed'});
      }
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('Rezervimi u krye me sukses! Po hapet faqja e pagesës...'),
          behavior: SnackBarBehavior.floating,
          backgroundColor: Colors.green,
        ));
      }
      final res = await Navigator.push(
        context,
        MaterialPageRoute(
          builder: (_) => PaymentFormPage(
            item: {
              'booking_id': bookingId,
              'amount': booking['total_price'],
              'customer_name': booking['customer_name'] ?? booking['customer']?['name'],
              'service_name': booking['service_name'] ?? booking['service']?['name'],
              'barber_shop_id': booking['barber_shop_id'],
              'method': 'cash',
              'status': 'completed',
            },
          ),
        ),
      );
      _refresh();
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text('Gabim: $e'),
          behavior: SnackBarBehavior.floating,
          backgroundColor: Colors.red,
        ));
      }
    }
  }

  Future<void> _updateBookingStatus(dynamic bookingId, String status) async {
    try {
      await ApiService.put('/bookings/$bookingId', {'status': status});
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text('Statusi u ndryshua në: ${status.toUpperCase()}'),
          behavior: SnackBarBehavior.floating,
          backgroundColor: Colors.green,
        ));
      }
      _refresh();
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          content: Text('Gabim gjatë përditësimit të statusit: $e'),
          behavior: SnackBarBehavior.floating,
          backgroundColor: Colors.red,
        ));
      }
    }
  }

  Color _paymentStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'paid': return Colors.green;
      case 'pending': return Colors.orange;
      case 'failed': return Colors.red;
      case 'refunded': return Colors.purple;
      default: return Colors.grey;
    }
  }

  IconData _paymentStatusIcon(String status) {
    switch (status.toLowerCase()) {
      case 'paid': return Icons.check_circle_rounded;
      case 'pending': return Icons.hourglass_top_rounded;
      case 'failed': return Icons.error_rounded;
      case 'refunded': return Icons.replay_rounded;
      default: return Icons.payment_rounded;
    }
  }
}
