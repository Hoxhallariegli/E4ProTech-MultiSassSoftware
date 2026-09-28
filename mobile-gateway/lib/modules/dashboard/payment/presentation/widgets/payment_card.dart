import 'package:flutter/material.dart';
import 'package:mobile_gateway/core/widgets/premium_widgets.dart';
import 'package:mobile_gateway/l10n/payment_localization.dart';
import 'package:mobile_gateway/core/branding/branding_cubit.dart';

class PaymentCard extends StatelessWidget {
  final Map<String, dynamic> item;
  final VoidCallback? onTap;
  final VoidCallback? onDelete;
  const PaymentCard({super.key, required this.item, this.onTap, this.onDelete});

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;

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

    final isCash = method == 'cash';
    Color statusColor = Colors.green;
    if (status == 'pending') statusColor = Colors.orange;
    if (status == 'failed') statusColor = Colors.red;
    if (status == 'refunded') statusColor = Colors.purple;

    return PremiumCard(
      onTap: onTap,
      padding: const EdgeInsets.all(14),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          Container(
            width: 48,
            height: 50,
            decoration: BoxDecoration(
              color: isCash ? Colors.green.withOpacity(0.12) : Colors.blue.withOpacity(0.12),
              borderRadius: BorderRadius.circular(16),
            ),
            child: Icon(
              isCash ? Icons.payments_rounded : Icons.credit_card_rounded,
              color: isCash ? Colors.green : Colors.blue,
              size: 24,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
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
                const SizedBox(height: 4),
                if (serviceName.isNotEmpty || bookingId != null)
                  Row(
                    children: [
                      Icon(Icons.content_cut_rounded, size: 13, color: theme.colorScheme.primary.withOpacity(0.85)),
                      const SizedBox(width: 4),
                      Expanded(
                        child: Text(
                          serviceName.isNotEmpty
                              ? (bookingId != null ? '$serviceName (Takimi #$bookingId)' : serviceName)
                              : 'Takimi #$bookingId',
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: TextStyle(
                            fontSize: 12.5,
                            fontWeight: FontWeight.w600,
                            color: isDark ? Colors.white70 : Colors.black87,
                          ),
                        ),
                      ),
                    ],
                  ),
                if (barberName.isNotEmpty) ...[
                  const SizedBox(height: 2),
                  Row(
                    children: [
                      Icon(Icons.person_outline_rounded, size: 13, color: Colors.grey.shade600),
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
                  ),
                ],
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
