import 'package:flutter/material.dart';
import 'package:mobile_gateway/services/api_service.dart';
import 'package:mobile_gateway/core/widgets/premium_widgets.dart';
import 'package:mobile_gateway/l10n/barber_shop_localization.dart';

class BarberShopCard extends StatelessWidget {
  final Map<String, dynamic> item;
  final VoidCallback? onTap;
  final VoidCallback? onDelete;
  const BarberShopCard({super.key, required this.item, this.onTap, this.onDelete});

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;

    final name = (item['name'] ?? item['title'] ?? 'Barber Shop').toString();
    final appName = item['app_name']?.toString() ?? '';
    final phone = item['phone']?.toString() ?? '';
    final logo = item['logo']?.toString();

    return PremiumCard(
      onTap: onTap,
      padding: const EdgeInsets.all(14),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          if (logo != null && logo.isNotEmpty)
            ClipRRect(
              borderRadius: BorderRadius.circular(16),
              child: Image.network(
                '${ApiService.serverUrl}/$logo',
                width: 52,
                height: 52,
                fit: BoxFit.cover,
                errorBuilder: (_, __, ___) => _Avatar(name: name),
              ),
            )
          else
            _Avatar(name: name),
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
                if (appName.isNotEmpty) ...[
                  const SizedBox(height: 3),
                  Text(
                    appName,
                    style: TextStyle(
                      fontSize: 12.5,
                      fontWeight: FontWeight.bold,
                      color: theme.colorScheme.primary,
                    ),
                  ),
                ],
                if (phone.isNotEmpty) ...[
                  const SizedBox(height: 3),
                  Row(
                    children: [
                      Icon(Icons.phone_rounded, size: 12, color: theme.colorScheme.primary),
                      const SizedBox(width: 4),
                      Text(
                        phone,
                        style: const TextStyle(fontSize: 12, color: Colors.grey),
                      ),
                    ],
                  ),
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
                if (onTap != null) PopupMenuItem(value: 'edit', child: Text(barber_shopTr(context, 'list.edit'))),
                if (onDelete != null) PopupMenuItem(value: 'delete', child: Text(barber_shopTr(context, 'list.delete'))),
              ],
            ),
        ],
      ),
    );
  }
}

class _Avatar extends StatelessWidget {
  final String name;
  const _Avatar({required this.name});

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final initial = name.trim().isEmpty ? 'S' : name.trim().substring(0, 1).toUpperCase();
    return Container(
      width: 52,
      height: 52,
      decoration: BoxDecoration(
        color: theme.colorScheme.primaryContainer.withOpacity(0.8),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Center(
        child: Text(
          initial,
          style: TextStyle(
            fontSize: 20,
            fontWeight: FontWeight.w900,
            color: theme.colorScheme.primary,
          ),
        ),
      ),
    );
  }
}
