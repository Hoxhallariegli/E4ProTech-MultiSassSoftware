import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../../core/widgets/app_scaffold.dart';
import '../../../../core/widgets/premium_header.dart';
import '../cubit/booking_cubit.dart';
import '../cubit/booking_state.dart';
import '../../data/booking_repository.dart';
import 'booking_form_page.dart';

class BookingCalendarPage extends StatefulWidget {
  const BookingCalendarPage({super.key});
  @override State<BookingCalendarPage> createState() => _BookingCalendarPageState();
}

class _BookingCalendarPageState extends State<BookingCalendarPage> with SingleTickerProviderStateMixin {
  late TabController _timelineTabController;
  DateTime _focusedDay = DateTime.now();
  DateTime _selectedDay = DateTime(DateTime.now().year, DateTime.now().month, DateTime.now().day);
  int? _selectedBarberId;

  @override
  void initState() {
    super.initState();
    _timelineTabController = TabController(length: 2, vsync: this, initialIndex: 1);
    final cubit = context.read<BookingCubit>();
    cubit.loadInitialData().then((_) {
      _refresh();
    });
  }

  void _refresh() {
    context.read<BookingCubit>().loadCalendarData(
      month: _focusedDay,
      day: _selectedDay,
      barberId: _selectedBarberId,
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

          return Column(
            children: [
              _buildBarberFilter(barbers),
              const SizedBox(height: 8),
              _buildCustomWeekCalendar(calendarStats),
              const SizedBox(height: 8),
              Container(
                width: double.infinity,
                decoration: BoxDecoration(
                  border: Border(bottom: BorderSide(color: theme.dividerColor, width: 1)),
                ),
                child: TabBar(
                  controller: _timelineTabController,
                  indicatorColor: theme.colorScheme.primary,
                  indicatorWeight: 3,
                  labelColor: theme.colorScheme.onSurface,
                  unselectedLabelColor: Colors.grey,
                  labelStyle: const TextStyle(fontWeight: FontWeight.w900, fontSize: 13, letterSpacing: 1),
                  tabs: const [Tab(text: 'HISTORIKU'), Tab(text: 'REZERVIMET')],
                  onTap: (i) => setState(() {}),
                ),
              ),
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
    return Container(
      height: 60,
      child: ListView(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
        children: [
          _barberChip('Të Gjithë', null),
          ...barbers.map((b) => _barberChip((b['name'] ?? 'Berber').toString(), int.tryParse(b['id']?.toString() ?? ''))),
        ],
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
    // Generate 14 days starting from 7 days ago to 7 days ahead
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

          final weekDays = ['Dje', 'Hën', 'Mar', 'Mër', 'Enj', 'Pre', 'Sht'];
          final dayName = weekDays[day.weekday % 7];

          return GestureDetector(
            onTap: () {
              final cleanDay = DateTime(day.year, day.month, day.day);
              setState(() {
                _selectedDay = cleanDay;
                _focusedDay = day;
              });
              context.read<BookingCubit>().fetchDaySlots(day: cleanDay, barberId: _selectedBarberId);
            },
            child: Container(
              width: 54,
              margin: const EdgeInsets.symmetric(horizontal: 4),
              decoration: BoxDecoration(
                color: isSelected ? theme.colorScheme.primary : theme.colorScheme.surfaceVariant.withOpacity(0.3),
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

    List<dynamic> filteredSlots = slots.where((slot) {
      final time = slot['time'].toString();
      final parts = time.split(':');
      final slotTime = DateTime(_selectedDay.year, _selectedDay.month, _selectedDay.day, int.parse(parts[0]), int.parse(parts[1]));

      if (_timelineTabController.index == 0) {
        if (isSelectedPast) return true;
        if (isSelectedToday) return slotTime.isBefore(now);
        return false;
      } else {
        if (isSelectedPast) return false;
        if (isSelectedToday) return slotTime.isAfter(now);
        return true;
      }
    }).toList();

    if (filteredSlots.isEmpty) {
      String msg = _timelineTabController.index == 0 ? "Nuk ka historik për këtë ditë." : "Nuk ka rezervime të ardhshme.";
      return Center(child: Padding(padding: const EdgeInsets.all(40), child: Text(msg, style: const TextStyle(color: Colors.grey, fontSize: 13))));
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: filteredSlots.length,
      itemBuilder: (context, index) {
        final slot = filteredSlots[index];
        final time = (slot['time'] ?? '00:00').toString();
        final isFree = slot['is_free'] == true;

        return Padding(
          padding: const EdgeInsets.symmetric(vertical: 6),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              SizedBox(
                width: 48,
                child: Padding(
                  padding: const EdgeInsets.only(top: 14),
                  child: Text(
                    time,
                    style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.grey, fontSize: 13),
                  ),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: isFree ? _buildFreeSlot(time) : _buildBookingCard(slot['booking'] ?? {}),
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildFreeSlot(String time) {
    final theme = Theme.of(context);
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: theme.colorScheme.surfaceVariant.withOpacity(0.15),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: theme.colorScheme.outlineVariant.withOpacity(0.4)),
      ),
      child: Row(
        children: [
          Icon(Icons.check_circle_outline_rounded, color: Colors.green.shade600, size: 18),
          const SizedBox(width: 8),
          const Text(
            'Ora e Lirë',
            style: TextStyle(fontWeight: FontWeight.w600, color: Colors.grey, fontSize: 13),
          ),
        ],
      ),
    );
  }

  Widget _buildBookingCard(Map<String, dynamic> booking) {
    final theme = Theme.of(context);
    final customer = booking['customer_name'] ?? 'Klient';
    final service = booking['service_name'] ?? 'Shërbim';
    final barber = booking['barber_name'] ?? 'Berber';
    final status = booking['status'] ?? 'pending';

    Color statusColor = Colors.orange;
    if (status == 'confirmed' || status == 'completed') statusColor = Colors.green;
    if (status == 'cancelled') statusColor = Colors.red;

    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: theme.colorScheme.surface,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.04),
            blurRadius: 8,
            offset: const Offset(0, 4),
          )
        ],
        border: Border.all(color: theme.colorScheme.outlineVariant),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.between,
            children: [
              Text(
                customer,
                style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 15),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: statusColor.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  status.toUpperCase(),
                  style: TextStyle(color: statusColor, fontWeight: FontWeight.bold, fontSize: 9),
                ),
              ),
            ],
          ),
          const SizedBox(height: 6),
          Text(
            service,
            style: TextStyle(color: theme.colorScheme.primary, fontWeight: FontWeight.bold, fontSize: 13),
          ),
          const SizedBox(height: 4),
          Row(
            children: [
              const Icon(Icons.person_outline_rounded, size: 12, color: Colors.grey),
              const SizedBox(width: 4),
              Text(
                barber,
                style: const TextStyle(color: Colors.grey, fontSize: 12),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
