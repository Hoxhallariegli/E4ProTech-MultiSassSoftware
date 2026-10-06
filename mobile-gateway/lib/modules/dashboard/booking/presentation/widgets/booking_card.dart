import 'package:flutter/material.dart';
import 'package:mobile_gateway/core/widgets/premium_widgets.dart';
import 'package:mobile_gateway/l10n/booking_localization.dart';
import 'package:mobile_gateway/core/branding/branding_cubit.dart';

class BookingCard extends StatelessWidget {
  final Map<String, dynamic> item;
  final VoidCallback? onTap;
  final VoidCallback? onDelete;
  const BookingCard({super.key, required this.item, this.onTap, this.onDelete});

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;

    final customer = (item['customer_name'] ?? item['customer']?['name'] ?? 'Klient').toString();
    final service = (item['service_name'] ?? item['service']?['name'] ?? 'Shërbim').toString();
    final barber = (item['barber_name'] ?? item['barber']?['name'] ?? context.staffLabel).toString();
    final status = (item['status'] ?? 'pending').toString();
    final totalPrice = item['total_price']?.toString();
    final appointmentAtStr = item['appointment_at']?.toString();
    final source = item['source']?.toString();
    final smsMessages = (item['sms_messages'] as List? ?? []);

    String formattedDate = '';
    if (appointmentAtStr != null && appointmentAtStr.isNotEmpty) {
      final parsed = DateTime.tryParse(appointmentAtStr.replaceAll('T', ' '))?.toLocal();
      if (parsed != null) {
        formattedDate = "${parsed.day}/${parsed.month}/${parsed.year} ${parsed.hour.toString().padLeft(2, '0')}:${parsed.minute.toString().padLeft(2, '0')}";
      } else {
        formattedDate = appointmentAtStr;
      }
    }

    Color statusColor = Colors.orange;
    if (status == 'completed') statusColor = Colors.green;
    if (status == 'confirmed') statusColor = Colors.blue;
    if (status == 'cancelled') statusColor = Colors.red;
    if (status == 'no-show') statusColor = Colors.purple;

    return PremiumCard(
      onTap: onTap,
      padding: const EdgeInsets.all(14),
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
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w900,
                        color: isDark ? Colors.white : theme.colorScheme.onSurface,
                      ),
                    ),
                    if (formattedDate.isNotEmpty) ...[
                      const SizedBox(height: 3),
                      Row(
                        children: [
                          Icon(Icons.access_time_rounded, size: 12, color: theme.colorScheme.primary),
                          const SizedBox(width: 4),
                          Text(
                            formattedDate,
                            style: TextStyle(
                              color: theme.colorScheme.primary,
                              fontWeight: FontWeight.bold,
                              fontSize: 12,
                            ),
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
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: statusColor.withOpacity(0.12),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Text(
                      status.toUpperCase(),
                      style: TextStyle(color: statusColor, fontWeight: FontWeight.bold, fontSize: 10),
                    ),
                  ),
                  if (onTap != null || onDelete != null)
                    PopupMenuButton<String>(
                      icon: Icon(Icons.more_vert_rounded, size: 20, color: isDark ? Colors.white70 : Colors.black54),
                      onSelected: (value) {
                        if (value == 'edit' && onTap != null) onTap!();
                        if (value == 'delete' && onDelete != null) onDelete!();
                      },
                      itemBuilder: (_) => [
                        if (onTap != null) PopupMenuItem(value: 'edit', child: Text(bookingTr(context, 'list.edit'))),
                        if (onDelete != null) PopupMenuItem(value: 'delete', child: Text(bookingTr(context, 'list.delete'))),
                      ],
                    ),
                ],
              ),
            ],
          ),
          const SizedBox(height: 8),
          Row(
            children: [
              Expanded(
                child: Text(
                  service,
                  style: TextStyle(color: theme.colorScheme.primary, fontWeight: FontWeight.bold, fontSize: 13.5),
                  overflow: TextOverflow.ellipsis,
                ),
              ),
              if (totalPrice != null && totalPrice.isNotEmpty) ...[
                const SizedBox(width: 8),
                Text(
                  '($totalPrice Lekë)',
                  style: TextStyle(color: theme.colorScheme.onSurfaceVariant, fontSize: 12, fontWeight: FontWeight.bold),
                ),
              ],
            ],
          ),
          const SizedBox(height: 5),
          Row(
            children: [
              const Icon(Icons.person_outline_rounded, size: 13, color: Colors.grey),
              const SizedBox(width: 4),
              Flexible(
                child: Text(
                  barber,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(color: Colors.grey, fontSize: 12),
                ),
              ),
              if (source != null && source.isNotEmpty) ...[
                const SizedBox(width: 8),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                  decoration: BoxDecoration(
                    color: theme.colorScheme.surfaceContainerHighest,
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Text(
                    source.toUpperCase(),
                    style: TextStyle(fontSize: 9.5, color: theme.colorScheme.onSurfaceVariant, fontWeight: FontWeight.bold),
                  ),
                ),
              ],
            ],
          ),

          // SMS Messages Status Badges
          if (smsMessages.isNotEmpty) ...[
            const SizedBox(height: 8),
            Wrap(
              spacing: 6,
              runSpacing: 4,
              children: smsMessages.map((msg) {
                final typeLabel = (msg['type_label'] ?? 'SMS').toString();
                final status = (msg['status'] ?? 'pending').toString().toLowerCase();

                Color color = Colors.amber.shade700;
                IconData icon = Icons.access_time_rounded;
                if (status == 'sent') {
                  color = Colors.green.shade600;
                  icon = Icons.check_circle_rounded;
                } else if (status == 'failed') {
                  color = Colors.red.shade600;
                  icon = Icons.cancel_rounded;
                }

                return Container(
                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                  decoration: BoxDecoration(
                    color: color.withOpacity(0.12),
                    borderRadius: BorderRadius.circular(6),
                    border: Border.all(color: color.withOpacity(0.3), width: 1),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(icon, size: 10, color: color),
                      const SizedBox(width: 3),
                      Text(
                        '$typeLabel: ${status.toUpperCase()}',
                        style: TextStyle(fontSize: 9.5, fontWeight: FontWeight.bold, color: color),
                      ),
                    ],
                  ),
                );
              }).toList(),
            ),
          ],
        ],
      ),
    );
  }
}
