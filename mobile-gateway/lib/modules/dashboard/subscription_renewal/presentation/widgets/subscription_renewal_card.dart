import 'package:flutter/material.dart';
import 'package:mobile_gateway/services/api_service.dart';
import 'package:mobile_gateway/core/widgets/premium_widgets.dart';

class SubscriptionRenewalCard extends StatelessWidget {
  final Map<String, dynamic> item;
  final VoidCallback? onTap;
  final VoidCallback? onDelete;

  const SubscriptionRenewalCard({
    super.key,
    required this.item,
    this.onTap,
    this.onDelete,
  });

  String _formatDate(String? raw) {
    if (raw == null || raw.isEmpty) return '';
    try {
      final dt = DateTime.parse(raw).toLocal();
      final day = dt.day.toString().padLeft(2, '0');
      final month = dt.month.toString().padLeft(2, '0');
      final year = dt.year;
      final hour = dt.hour.toString().padLeft(2, '0');
      final minute = dt.minute.toString().padLeft(2, '0');
      return '$day/$month/$year $hour:$minute';
    } catch (_) {
      return raw;
    }
  }

  void _showFullImage(BuildContext context, String imageUrl) {
    showDialog(
      context: context,
      builder: (_) => Dialog(
        backgroundColor: Colors.transparent,
        child: ClipRRect(
          borderRadius: BorderRadius.circular(20),
          child: Image.network(imageUrl, fit: BoxFit.contain),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    final primaryColor = theme.colorScheme.primary;

    final planName = (item['plan']?['name'] ?? 'Plani i Abonimit').toString();
    final shopName = (item['barberShop']?['name'] ?? item['barber_shop']?['name'] ?? '').toString();
    final amount = (item['amount'] ?? '0').toString();
    final method = (item['payment_method'] ?? 'bank_transfer').toString();
    final status = (item['status'] ?? 'pending').toString().toLowerCase();
    final documentUrl = item['transfer_document']?.toString();
    final notes = (item['notes'] ?? '').toString();
    final createdAt = _formatDate(item['created_at']?.toString());

    Color statusColor = Colors.amber.shade700;
    String statusLabel = '⏳ NË PRITJE';
    if (status == 'approved') {
      statusColor = Colors.green.shade600;
      statusLabel = '✅ I MIRATUAR';
    } else if (status == 'rejected') {
      statusColor = Colors.red.shade600;
      statusLabel = '❌ I REFUZUAR';
    }

    String methodLabel = '🏦 Transfertë Bankare';
    if (method == 'cash') methodLabel = '💵 Cash (Në Dorë)';
    if (method == 'card') methodLabel = '💳 Kartë';
    if (method == 'online') methodLabel = '🌐 Online';

    final fullDocUrl = (documentUrl != null && documentUrl.isNotEmpty)
        ? (documentUrl.startsWith('http') ? documentUrl : '${ApiService.serverUrl}/$documentUrl')
        : null;

    return PremiumCard(
      onTap: onTap,
      padding: const EdgeInsets.all(14),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              if (fullDocUrl != null)
                GestureDetector(
                  onTap: () => _showFullImage(context, fullDocUrl),
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(14),
                    child: Image.network(
                      fullDocUrl,
                      width: 52,
                      height: 52,
                      fit: BoxFit.cover,
                      errorBuilder: (_, __, ___) => _DefaultIcon(color: primaryColor),
                    ),
                  ),
                )
              else
                _DefaultIcon(color: primaryColor),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      planName,
                      style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w900),
                      overflow: TextOverflow.ellipsis,
                    ),
                    if (shopName.isNotEmpty) ...[
                      const SizedBox(height: 2),
                      Text(
                        shopName,
                        style: TextStyle(fontSize: 11.5, color: isDark ? Colors.white70 : Colors.black87, fontWeight: FontWeight.w600),
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                  ],
                ),
              ),

              const SizedBox(width: 8),

              // Status Badge
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: statusColor.withOpacity(0.12),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: statusColor.withOpacity(0.3)),
                ),
                child: Text(
                  statusLabel,
                  style: TextStyle(fontSize: 10, fontWeight: FontWeight.w900, color: statusColor),
                ),
              ),

              if (onTap != null || onDelete != null)
                PopupMenuButton<String>(
                  padding: EdgeInsets.zero,
                  icon: Icon(Icons.more_vert_rounded, size: 20, color: isDark ? Colors.white70 : Colors.black54),
                  onSelected: (value) {
                    if (value == 'edit' && onTap != null) onTap!();
                    if (value == 'delete' && onDelete != null) onDelete!();
                  },
                  itemBuilder: (_) => [
                    if (onTap != null) const PopupMenuItem(value: 'edit', child: Text('✏️ Redakto')),
                    if (onDelete != null) const PopupMenuItem(value: 'delete', child: Text('🗑️ Fshi Kërkesën')),
                  ],
                ),
            ],
          ),

          const SizedBox(height: 10),

          // Details Row: Method + Amount + Date
          Wrap(
            spacing: 8,
            runSpacing: 6,
            children: [
              _InfoBadge(icon: Icons.payments_rounded, label: methodLabel, color: primaryColor),
              _InfoBadge(icon: Icons.sell_rounded, label: '$amount Lekë', color: Colors.green.shade700),
              if (createdAt.isNotEmpty)
                _InfoBadge(icon: Icons.schedule_rounded, label: createdAt, color: Colors.grey.shade700),
            ],
          ),

          if (notes.isNotEmpty) ...[
            const SizedBox(height: 8),
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: isDark ? Colors.white.withOpacity(0.05) : Colors.grey.shade100,
                borderRadius: BorderRadius.circular(8),
              ),
              child: Text(
                'Shënime: $notes',
                style: TextStyle(fontSize: 11, fontStyle: FontStyle.italic, color: isDark ? Colors.white70 : Colors.grey.shade800),
              ),
            ),
          ],
        ],
      ),
    );
  }
}

class _DefaultIcon extends StatelessWidget {
  final Color color;
  const _DefaultIcon({required this.color});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 52,
      height: 52,
      decoration: BoxDecoration(
        color: color.withOpacity(0.12),
        borderRadius: BorderRadius.circular(14),
      ),
      child: Icon(Icons.receipt_long_rounded, color: color, size: 26),
    );
  }
}

class _InfoBadge extends StatelessWidget {
  final IconData icon;
  final String label;
  final Color color;

  const _InfoBadge({required this.icon, required this.label, required this.color});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: color.withOpacity(0.1),
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: color.withOpacity(0.2)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 12, color: color),
          const SizedBox(width: 4),
          Text(label, style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: color)),
        ],
      ),
    );
  }
}
