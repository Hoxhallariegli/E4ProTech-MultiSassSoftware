import 'package:flutter/material.dart';
import 'package:mobile_gateway/core/widgets/premium_widgets.dart';

class DeviceTokenCard extends StatelessWidget {
  final Map<String, dynamic> item;
  final VoidCallback? onTap;
  final VoidCallback? onDelete;
  const DeviceTokenCard({super.key, required this.item, this.onTap, this.onDelete});

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    final primaryColor = theme.colorScheme.primary;

    final deviceName = (item['device_name'] ?? item['name'] ?? 'Pajisje Mobile').toString();
    final userName = (item['user_name'] ?? item['user']?['name'] ?? '').toString();
    final platform = (item['platform'] ?? 'android').toString().toUpperCase();
    final isGateway = item['is_sms_gateway'] == true || item['is_sms_gateway'] == 1 || item['is_sms_gateway'] == '1';

    const gatewayColor = Color(0xFF059669);

    return PremiumCard(
      onTap: onTap,
      padding: const EdgeInsets.all(12),
      child: Row(
        children: [
          Container(
            width: 48,
            height: 48,
            decoration: BoxDecoration(
              color: isGateway ? gatewayColor.withOpacity(0.15) : primaryColor.withOpacity(0.12),
              borderRadius: BorderRadius.circular(14),
            ),
            child: Icon(
              isGateway ? Icons.smartphone_rounded : Icons.phone_android_rounded,
              color: isGateway ? gatewayColor : primaryColor,
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
                        deviceName,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: TextStyle(
                          fontSize: 15,
                          fontWeight: FontWeight.w900,
                          color: isDark ? Colors.white : theme.colorScheme.onSurface,
                        ),
                      ),
                    ),
                    const SizedBox(width: 6),
                    if (isGateway)
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                        decoration: BoxDecoration(
                          color: gatewayColor.withOpacity(0.15),
                          borderRadius: BorderRadius.circular(6),
                          border: Border.all(color: gatewayColor.withOpacity(0.4)),
                        ),
                        child: const Text(
                          '📱 SMS GATEWAY',
                          style: TextStyle(fontSize: 9, fontWeight: FontWeight.w900, color: gatewayColor),
                        ),
                      )
                    else
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                        decoration: BoxDecoration(
                          color: theme.colorScheme.surfaceContainerHighest,
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Text(
                          platform,
                          style: TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: theme.colorScheme.onSurfaceVariant),
                        ),
                      ),
                  ],
                ),
                if (userName.isNotEmpty) ...[
                  const SizedBox(height: 3),
                  Text(
                    'Përdoruesi: $userName',
                    style: TextStyle(fontSize: 11.5, color: isDark ? Colors.grey.shade400 : Colors.grey.shade600, fontWeight: FontWeight.w500),
                  ),
                ],
              ],
            ),
          ),
          if (onTap != null || onDelete != null)
            PopupMenuButton<String>(
              padding: EdgeInsets.zero,
              icon: Icon(Icons.more_vert_rounded, color: isDark ? Colors.white70 : Colors.black54),
              onSelected: (value) {
                if (value == 'edit' && onTap != null) onTap!();
                if (value == 'delete' && onDelete != null) onDelete!();
              },
              itemBuilder: (_) => [
                if (onTap != null) const PopupMenuItem(value: 'edit', child: Text('✏️ Edito')),
                if (onDelete != null) const PopupMenuItem(value: 'delete', child: Text('🗑️ Fshi')),
              ],
            ),
        ],
      ),
    );
  }
}
