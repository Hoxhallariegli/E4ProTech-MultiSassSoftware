import 'package:flutter/material.dart';
import 'package:mobile_gateway/services/api_service.dart';
import 'package:mobile_gateway/core/widgets/premium_widgets.dart';
import 'package:mobile_gateway/l10n/barber_localization.dart';
import 'package:mobile_gateway/l10n/core_localization.dart';
import 'package:mobile_gateway/core/branding/branding_cubit.dart';

class BarberCard extends StatelessWidget {
  final Map<String, dynamic> item;
  final VoidCallback? onTap;
  final VoidCallback? onDelete;
  const BarberCard({super.key, required this.item, this.onTap, this.onDelete});

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;

    final name = (item['name'] ?? item['title'] ?? context.staffLabel).toString();
    final phone = (item['phone'] ?? '').toString();
    final photo = item['photo']?.toString();
    final bio = item['bio']?.toString();
    final bool active = item['active'] == true || item['active'] == 1 || item['active'] == '1' || item['active'] == null;

    return PremiumCard(
      onTap: onTap,
      padding: const EdgeInsets.all(14),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          if (photo != null && photo.isNotEmpty)
            ClipRRect(
              borderRadius: BorderRadius.circular(16),
              child: Image.network(
                '${ApiService.serverUrl}/$photo',
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
                Row(
                  children: [
                    Expanded(
                      child: Text(
                        name,
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
                        color: active ? Colors.green.withOpacity(0.12) : Colors.grey.withOpacity(0.12),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        active ? coreTr(context, 'common.active') : coreTr(context, 'common.inactive'),
                        style: TextStyle(
                          fontSize: 9.5,
                          fontWeight: FontWeight.bold,
                          color: active ? Colors.green : Colors.grey,
                        ),
                      ),
                    ),
                  ],
                ),
                if (phone.isNotEmpty) ...[
                  const SizedBox(height: 5),
                  Row(
                    children: [
                      Icon(Icons.phone_rounded, size: 13, color: theme.colorScheme.primary),
                      const SizedBox(width: 5),
                      Text(
                        phone,
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w600,
                          color: isDark ? const Color(0xFFCBD5E1) : theme.colorScheme.onSurfaceVariant,
                        ),
                      ),
                    ],
                  ),
                ],
                if (bio != null && bio.isNotEmpty) ...[
                  const SizedBox(height: 4),
                  Text(
                    bio,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(fontSize: 11.5, color: Colors.grey),
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
                if (onTap != null) PopupMenuItem(value: 'edit', child: Text(barberTr(context, 'list.edit'))),
                if (onDelete != null) PopupMenuItem(value: 'delete', child: Text(barberTr(context, 'list.delete'))),
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
    final initial = name.trim().isEmpty ? 'B' : name.trim().substring(0, 1).toUpperCase();
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
