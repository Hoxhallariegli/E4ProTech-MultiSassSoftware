import 'package:flutter/material.dart';
import 'package:mobile_gateway/core/widgets/premium_widgets.dart';
import 'package:mobile_gateway/l10n/bulk_cancellation_localization.dart';

class BulkCancellationCard extends StatelessWidget {
  final Map<String, dynamic> item;
  final VoidCallback? onTap;
  final VoidCallback? onDelete;
  const BulkCancellationCard({super.key, required this.item, this.onTap, this.onDelete});

  String get barberName => (item['barber']?['name'] ?? 'Anëtar Stafi').toString();
  String get shopName => (item['barber_shop']?['name'] ?? item['barberShop']?['name'] ?? 'Sallon').toString();
  String get reason => (item['reason'] ?? 'Mungesë Emergjenë Stafi').toString();
  int get affectedCount => int.tryParse(item['affected_count']?.toString() ?? '0') ?? 0;

  List<String> get bookingIds {
    final raw = item['booking_ids'];
    if (raw is List) {
      return raw.map((e) => '#$e').toList();
    }
    return [];
  }

  String _formatDateTime(dynamic value) {
    if (value == null || value.toString().trim().isEmpty) return '-';
    try {
      final parsed = DateTime.tryParse(value.toString())?.toLocal();
      if (parsed == null) return value.toString();
      return '${parsed.day.toString().padLeft(2, '0')}/${parsed.month.toString().padLeft(2, '0')}/${parsed.year} ${parsed.hour.toString().padLeft(2, '0')}:${parsed.minute.toString().padLeft(2, '0')}';
    } catch (_) {
      return value.toString();
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;

    final startStr = _formatDateTime(item['start_at']);
    final endStr = _formatDateTime(item['end_at']);

    return PremiumCard(
      onTap: onTap,
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 44,
                height: 44,
                decoration: BoxDecoration(
                  color: Colors.redAccent.withOpacity(0.12),
                  borderRadius: BorderRadius.circular(14),
                ),
                child: const Icon(Icons.event_busy_rounded, color: Colors.redAccent, size: 22),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      barberName,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w900),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      shopName,
                      style: TextStyle(fontSize: 11, color: theme.colorScheme.onSurfaceVariant, fontWeight: FontWeight.w600),
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                decoration: BoxDecoration(
                  color: Colors.redAccent.withOpacity(0.12),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: Colors.redAccent.withOpacity(0.3)),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(Icons.cancel_outlined, size: 13, color: Colors.redAccent),
                    const SizedBox(width: 4),
                    Text(
                      '$affectedCount Takime',
                      style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w900, color: Colors.redAccent),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: isDark ? const Color(0xFF1E212B) : theme.colorScheme.surfaceContainerHighest.withOpacity(0.4),
              borderRadius: BorderRadius.circular(14),
            ),
            child: Column(
              children: [
                Row(
                  children: [
                    const Icon(Icons.access_time_rounded, size: 15, color: Colors.grey),
                    const SizedBox(width: 6),
                    Expanded(
                      child: Text(
                        '$startStr  ➔  $endStr',
                        style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 6),
                Row(
                  children: [
                    const Icon(Icons.info_outline_rounded, size: 15, color: Colors.grey),
                    const SizedBox(width: 6),
                    Expanded(
                      child: Text(
                        'Arsyeja: $reason',
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: TextStyle(fontSize: 11.5, color: theme.colorScheme.onSurfaceVariant),
                      ),
                    ),
                  ],
                ),
                if (bookingIds.isNotEmpty) ...[
                  const SizedBox(height: 6),
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Icon(Icons.format_list_bulleted_rounded, size: 15, color: Colors.blueAccent),
                      const SizedBox(width: 6),
                      Expanded(
                        child: Text(
                          'Takimet e anuluara: ${bookingIds.join(", ")}',
                          style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.blueAccent),
                        ),
                      ),
                    ],
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }
}
