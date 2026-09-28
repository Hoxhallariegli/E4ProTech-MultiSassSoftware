import 'package:flutter/material.dart';
import 'package:mobile_gateway/core/widgets/premium_widgets.dart';
import 'package:mobile_gateway/l10n/review_localization.dart';

class ReviewCard extends StatelessWidget {
  final Map<String, dynamic> item;
  final VoidCallback? onTap;
  final VoidCallback? onDelete;
  const ReviewCard({super.key, required this.item, this.onTap, this.onDelete});

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;

    final customer = (item['customer_name'] ?? item['customer']?['name'] ?? 'Klient').toString();
    final barber = (item['barber_name'] ?? item['barber']?['name'] ?? '').toString();
    final comment = (item['comment'] ?? '').toString();
    final rawRating = item['rating'];
    final rating = rawRating != null ? int.tryParse(rawRating.toString()) ?? 5 : 5;

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
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w900,
                        color: isDark ? Colors.white : theme.colorScheme.onSurface,
                      ),
                    ),
                    if (barber.isNotEmpty) ...[
                      const SizedBox(height: 2),
                      Text(
                        'Për: $barber',
                        style: const TextStyle(fontSize: 11.5, color: Colors.grey),
                      ),
                    ],
                  ],
                ),
              ),
              Row(
                mainAxisSize: MainAxisSize.min,
                children: List.generate(
                  5,
                  (index) => Icon(
                    index < rating ? Icons.star_rounded : Icons.star_border_rounded,
                    color: Colors.amber,
                    size: 16,
                  ),
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
                    if (onTap != null) PopupMenuItem(value: 'edit', child: Text(reviewTr(context, 'list.edit'))),
                    if (onDelete != null) PopupMenuItem(value: 'delete', child: Text(reviewTr(context, 'list.delete'))),
                  ],
                ),
            ],
          ),
          if (comment.isNotEmpty) ...[
            const SizedBox(height: 8),
            Text(
              '"$comment"',
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
              style: TextStyle(
                fontSize: 13,
                fontStyle: FontStyle.italic,
                color: isDark ? const Color(0xFFCBD5E1) : theme.colorScheme.onSurfaceVariant,
              ),
            ),
          ],
        ],
      ),
    );
  }
}
