import 'package:flutter/material.dart';
import 'package:mobile_gateway/core/widgets/premium_widgets.dart';
import 'package:mobile_gateway/l10n/plan_localization.dart';

class PlanCard extends StatelessWidget {
  final Map<String, dynamic> item;
  final VoidCallback? onTap;
  final VoidCallback? onDelete;
  const PlanCard({super.key, required this.item, this.onTap, this.onDelete});

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;

    final name = (item['name'] ?? item['title'] ?? 'Plan').toString();
    final rawPrice = item['price'];
    final priceStr = rawPrice != null && double.tryParse(rawPrice.toString()) != null
        ? '${double.parse(rawPrice.toString()).toStringAsFixed(2)} Lekë'
        : '0.00 Lekë';
    final months = item['duration_months']?.toString() ?? '1';

    return PremiumCard(
      onTap: onTap,
      padding: const EdgeInsets.all(14),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          Container(
            width: 50,
            height: 52,
            decoration: BoxDecoration(
              color: theme.colorScheme.primary.withOpacity(0.12),
              borderRadius: BorderRadius.circular(16),
            ),
            child: Icon(
              Icons.stars_rounded,
              color: theme.colorScheme.primary,
              size: 26,
            ),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  name,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w900,
                    color: isDark ? Colors.white : theme.colorScheme.onSurface,
                  ),
                ),
                const SizedBox(height: 5),
                Row(
                  children: [
                    Text(
                      priceStr,
                      style: TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.w900,
                        color: theme.colorScheme.primary,
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
                        '$months Muaj',
                        style: TextStyle(
                          fontSize: 10.5,
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
                if (onTap != null) PopupMenuItem(value: 'edit', child: Text(planTr(context, 'list.edit'))),
                if (onDelete != null) PopupMenuItem(value: 'delete', child: Text(planTr(context, 'list.delete'))),
              ],
            ),
        ],
      ),
    );
  }
}
