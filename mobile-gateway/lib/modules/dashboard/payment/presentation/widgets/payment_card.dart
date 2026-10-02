import 'package:flutter/material.dart';
import 'package:mobile_gateway/core/widgets/premium_widgets.dart';
import 'package:mobile_gateway/l10n/payment_localization.dart';
import 'package:mobile_gateway/core/branding/branding_cubit.dart';

class PaymentCard extends StatelessWidget {
  final Map<String, dynamic> item;
  final VoidCallback? onTap;
  final VoidCallback? onDelete;
  const PaymentCard({super.key, required this.item, this.onTap, this.onDelete});

  String _formatDateTime(String? raw) {
    if (raw == null || raw.trim().isEmpty) return '';
    try {
      final dt = DateTime.parse(raw.trim()).toLocal();
      final now = DateTime.now();

      final hour = dt.hour.toString().padLeft(2, '0');
      final minute = dt.minute.toString().padLeft(2, '0');
      final timeStr = '$hour:$minute';

      final isToday = dt.year == now.year && dt.month == now.month && dt.day == now.day;
      final yesterday = now.subtract(const Duration(days: 1));
      final isYesterday = dt.year == yesterday.year && dt.month == yesterday.month && dt.day == yesterday.day;

      if (isToday) {
        return 'Sot në $timeStr';
      } else if (isYesterday) {
        return 'Dje në $timeStr';
      } else {
        final day = dt.day.toString().padLeft(2, '0');
        final month = dt.month.toString().padLeft(2, '0');
        final year = dt.year;
        return '$day/$month/$year $timeStr';
      }
    } catch (_) {
      return raw;
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    final primaryColor = theme.colorScheme.primary;

    final rawAmount = item['amount'];
    final amountStr = rawAmount != null && double.tryParse(rawAmount.toString()) != null
        ? '${double.parse(rawAmount.toString()).toStringAsFixed(2)} Lekë'
        : '0.00 Lekë';
    final method = (item['method'] ?? 'cash').toString().toLowerCase();
    final status = (item['status'] ?? 'paid').toString().toLowerCase();

    final booking = item['booking'] as Map<String, dynamic>?;
    final customerObj = booking?['customer'] as Map<String, dynamic>?;
    final customerName = (item['customer_name'] ?? customerObj?['name'] ?? booking?['customer_name'] ?? booking?['name'] ?? '').toString().trim();

    final serviceObj = booking?['service'] as Map<String, dynamic>?;
    String serviceName = (item['service_name'] ?? serviceObj?['name'] ?? booking?['service_name'] ?? '').toString().trim();
    final notes = (booking?['notes'] ?? item['notes'] ?? '').toString().trim();
    if (notes.startsWith('Shërbimet: ')) {
      serviceName = notes.substring('Shërbimet: '.length).trim();
    }

    final barberObj = booking?['barber'] as Map<String, dynamic>?;
    final barberName = (item['barber_name'] ?? barberObj?['name'] ?? booking?['barber_name'] ?? '').toString().trim();
    final bookingId = item['booking_id'] ?? booking?['id'];
    final createdAtRaw = item['created_at']?.toString();
    final formattedDate = _formatDateTime(createdAtRaw);

    final isCash = method == 'cash';
    Color statusColor = Colors.green.shade600;
    if (status == 'pending') statusColor = Colors.orange.shade700;
    if (status == 'failed') statusColor = Colors.red.shade600;
    if (status == 'refunded') statusColor = Colors.purple.shade600;

    return PremiumCard(
      onTap: onTap,
      padding: const EdgeInsets.all(14),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          // Dynamic Payment Method Icon Container
          Container(
            width: 44,
            height: 46,
            decoration: BoxDecoration(
              color: isCash ? Colors.green.withOpacity(0.12) : primaryColor.withOpacity(0.12),
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: (isCash ? Colors.green : primaryColor).withOpacity(0.3)),
            ),
            child: Icon(
              isCash ? Icons.payments_rounded : Icons.credit_card_rounded,
              color: isCash ? Colors.green.shade600 : primaryColor,
              size: 22,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Row 1: Customer Name + Status Badge
                Row(
                  children: [
                    Expanded(
                      child: Text(
                        customerName.isNotEmpty ? customerName : (bookingId != null ? 'Takimi #$bookingId' : 'Pagesë'),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: TextStyle(
                          fontSize: 15.5,
                          fontWeight: FontWeight.w900,
                          color: isDark ? Colors.white : theme.colorScheme.onSurface,
                        ),
                      ),
                    ),
                    const SizedBox(width: 6),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                      decoration: BoxDecoration(
                        color: statusColor.withOpacity(0.12),
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: statusColor.withOpacity(0.3)),
                      ),
                      child: Text(
                        status.toUpperCase(),
                        style: TextStyle(
                          fontSize: 9.5,
                          fontWeight: FontWeight.bold,
                          color: statusColor,
                        ),
                      ),
                    ),
                  ],
                ),

                // Row 2: Service Name in Primary Branding Accent
                if (serviceName.isNotEmpty) ...[
                  const SizedBox(height: 4),
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Padding(
                        padding: const EdgeInsets.only(top: 2),
                        child: Icon(Icons.content_cut_rounded, size: 13, color: primaryColor),
                      ),
                      const SizedBox(width: 5),
                      Expanded(
                        child: Text(
                          serviceName,
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                          style: TextStyle(
                            fontSize: 13,
                            height: 1.3,
                            fontWeight: FontWeight.w700,
                            color: primaryColor,
                          ),
                        ),
                      ),
                    ],
                  ),
                ],

                // Row 3: Staff Name & Booking ID
                const SizedBox(height: 4),
                Row(
                  children: [
                    if (barberName.isNotEmpty) ...[
                      Icon(Icons.person_outline_rounded, size: 12, color: isDark ? Colors.grey.shade400 : Colors.grey.shade600),
                      const SizedBox(width: 4),
                      Expanded(
                        child: Text(
                          '${context.staffLabel}: $barberName',
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: TextStyle(
                            fontSize: 11.5,
                            color: isDark ? Colors.grey.shade400 : Colors.grey.shade700,
                          ),
                        ),
                      ),
                    ],
                    if (bookingId != null) ...[
                      if (barberName.isNotEmpty) const SizedBox(width: 6),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                        decoration: BoxDecoration(
                          color: theme.colorScheme.surfaceContainerHighest,
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Text(
                          'Takimi #$bookingId',
                          style: TextStyle(
                            fontSize: 10,
                            fontWeight: FontWeight.bold,
                            color: theme.colorScheme.onSurfaceVariant,
                          ),
                        ),
                      ),
                    ],
                  ],
                ),

                // Row 4: Amount + Method + Date
                const SizedBox(height: 6),
                Row(
                  children: [
                    Text(
                      amountStr,
                      style: TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.w900,
                        color: Colors.green.shade600,
                      ),
                    ),
                    const SizedBox(width: 8),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(
                        color: theme.colorScheme.surfaceContainerHighest,
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Text(
                        method.toUpperCase(),
                        style: TextStyle(
                          fontSize: 10,
                          fontWeight: FontWeight.bold,
                          color: theme.colorScheme.onSurfaceVariant,
                        ),
                      ),
                    ),
                    if (formattedDate.isNotEmpty) ...[
                      const SizedBox(width: 8),
                      Expanded(
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.end,
                          children: [
                            Icon(Icons.access_time_rounded, size: 11, color: isDark ? Colors.grey.shade400 : Colors.grey.shade500),
                            const SizedBox(width: 3),
                            Flexible(
                              child: Text(
                                formattedDate,
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.bold,
                                  color: isDark ? Colors.grey.shade400 : Colors.grey.shade500,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ],
                ),
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
                if (onTap != null) PopupMenuItem(value: 'edit', child: Text(paymentTr(context, 'list.edit'))),
                if (onDelete != null) PopupMenuItem(value: 'delete', child: Text(paymentTr(context, 'list.delete'))),
              ],
            ),
        ],
      ),
    );
  }
}
