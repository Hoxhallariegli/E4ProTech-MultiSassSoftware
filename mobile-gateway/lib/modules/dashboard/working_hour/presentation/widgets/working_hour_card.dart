import 'package:flutter/material.dart';
import 'package:mobile_gateway/core/widgets/premium_widgets.dart';
import 'package:mobile_gateway/l10n/working_hour_localization.dart';
import 'package:mobile_gateway/core/branding/branding_cubit.dart';

class WorkingHourCard extends StatelessWidget {
  final Map<String, dynamic> item;
  final VoidCallback? onTap;
  final VoidCallback? onDelete;
  const WorkingHourCard({super.key, required this.item, this.onTap, this.onDelete});

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;

    String formatTime(String? raw) {
      if (raw == null || raw.trim().isEmpty) return '';
      final parts = raw.trim().split(':');
      if (parts.length >= 2) {
        return '${parts[0].padLeft(2, '0')}:${parts[1].padLeft(2, '0')}';
      }
      return raw.trim();
    }

    final barberName = (item['barber_name'] ?? item['barber']?['name'] ?? context.staffLabel).toString();
    final dayOfWeek = (item['day_of_week'] ?? 'Monday').toString();
    final openTime = formatTime(item['open_time']?.toString() ?? '08:00');
    final closeTime = formatTime(item['close_time']?.toString() ?? '20:00');
    final lunchStart = formatTime(item['lunch_start']?.toString());
    final lunchEnd = formatTime(item['lunch_end']?.toString());
    final bool isClosed = item['is_closed'] == true || item['is_closed'] == 1 || item['is_closed'] == '1' || item['is_closed'] == 'true';

    final shortDay = dayOfWeek.length >= 3 ? dayOfWeek.substring(0, 3).toUpperCase() : dayOfWeek.toUpperCase();

    return PremiumCard(
      onTap: onTap,
      padding: const EdgeInsets.all(14),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          Container(
            width: 52,
            height: 52,
            decoration: BoxDecoration(
              color: isClosed ? Colors.red.withOpacity(0.12) : theme.colorScheme.primaryContainer.withOpacity(0.8),
              borderRadius: BorderRadius.circular(16),
            ),
            child: Center(
              child: Text(
                shortDay,
                style: TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w900,
                  color: isClosed ? Colors.red : theme.colorScheme.primary,
                ),
              ),
            ),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Expanded(
                      child: Text(
                        barberName,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.w900,
                          color: isDark ? Colors.white : theme.colorScheme.onSurface,
                        ),
                      ),
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                      decoration: BoxDecoration(
                        color: isClosed ? Colors.red.withOpacity(0.12) : Colors.green.withOpacity(0.12),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        isClosed ? 'PUSHIM' : 'HAPUR',
                        style: TextStyle(
                          fontSize: 9.5,
                          fontWeight: FontWeight.bold,
                          color: isClosed ? Colors.red : Colors.green,
                        ),
                      ),
                    ),
                  ],
                ),
                if (!isClosed) ...[
                  const SizedBox(height: 5),
                  Row(
                    children: [
                      Icon(Icons.schedule_rounded, size: 13, color: theme.colorScheme.primary),
                      const SizedBox(width: 5),
                      Text(
                        '$openTime - $closeTime',
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.bold,
                          color: theme.colorScheme.primary,
                        ),
                      ),
                    ],
                  ),
                  if (lunchStart.isNotEmpty && lunchEnd.isNotEmpty) ...[
                    const SizedBox(height: 3),
                    Row(
                      children: [
                        const Icon(Icons.restaurant_rounded, size: 12, color: Colors.amber),
                        const SizedBox(width: 5),
                        Text(
                          'Dreka: $lunchStart - $lunchEnd',
                          style: const TextStyle(fontSize: 11.5, color: Colors.amber, fontWeight: FontWeight.bold),
                        ),
                      ],
                    ),
                  ],
                ],
              ],
            ),
          ),
          if (onTap != null || onDelete != null)
            PopupMenuButton<String>(
              icon: Icon(Icons.more_vert_rounded, color: isDark ? Colors.white70 : Colors.black54),
              onSelected: (value) {
                if (value == 'edit' && onTap != null) onTap!();
                if (value == 'delete' && onDelete != null) onDelete!();
              },
              itemBuilder: (_) => [
                if (onTap != null) PopupMenuItem(value: 'edit', child: Text(working_hourTr(context, 'list.edit'))),
                if (onDelete != null) PopupMenuItem(value: 'delete', child: Text(working_hourTr(context, 'list.delete'))),
              ],
            ),
        ],
      ),
    );
  }
}
