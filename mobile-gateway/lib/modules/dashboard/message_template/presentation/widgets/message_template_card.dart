import 'package:flutter/material.dart';
import 'package:mobile_gateway/core/widgets/premium_widgets.dart';

class MessageTemplateCard extends StatelessWidget {
  final Map<String, dynamic> item;
  final VoidCallback? onTap;
  final VoidCallback? onDelete;

  const MessageTemplateCard({
    super.key,
    required this.item,
    this.onTap,
    this.onDelete,
  });

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    final primaryColor = theme.colorScheme.primary;

    final channel = (item['channel'] ?? 'sms').toString().toLowerCase();
    final type = (item['type'] ?? 'confirmation').toString().toLowerCase();
    final content = (item['content'] ?? '').toString();

    IconData channelIcon = Icons.sms_rounded;
    Color channelColor = primaryColor;
    String channelLabel = 'SMS';

    if (channel == 'whatsapp') {
      channelIcon = Icons.chat_bubble_rounded;
      channelColor = const Color(0xFF25D366);
      channelLabel = 'WhatsApp';
    } else if (channel == 'email') {
      channelIcon = Icons.email_rounded;
      channelColor = Colors.amber.shade700;
      channelLabel = 'Email';
    }

    Color typeColor = primaryColor;
    String typeLabel = 'Konfirmim';
    if (type == 'reminder') {
      typeColor = Colors.amber.shade700;
      typeLabel = 'Rikujtesë';
    } else if (type == 'welcome') {
      typeColor = Colors.green.shade600;
      typeLabel = 'Mirëseardhje';
    }

    return PremiumCard(
      onTap: onTap,
      padding: const EdgeInsets.all(12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header Row: Channel Icon + Title + Badges
          Row(
            children: [
              Icon(channelIcon, size: 16, color: channelColor),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  typeLabel,
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

              // Channel Badge
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                decoration: BoxDecoration(
                  color: channelColor.withOpacity(0.12),
                  borderRadius: BorderRadius.circular(6),
                  border: Border.all(color: channelColor.withOpacity(0.3)),
                ),
                child: Text(
                  channelLabel,
                  style: TextStyle(
                    fontSize: 9.5,
                    fontWeight: FontWeight.bold,
                    color: channelColor,
                  ),
                ),
              ),

              const SizedBox(width: 4),

              // Type Badge
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                decoration: BoxDecoration(
                  color: typeColor.withOpacity(0.12),
                  borderRadius: BorderRadius.circular(6),
                  border: Border.all(color: typeColor.withOpacity(0.3)),
                ),
                child: Text(
                  typeLabel.toUpperCase(),
                  style: TextStyle(
                    fontSize: 9.5,
                    fontWeight: FontWeight.bold,
                    color: typeColor,
                  ),
                ),
              ),

              if (onTap != null || onDelete != null)
                PopupMenuButton<String>(
                  padding: EdgeInsets.zero,
                  icon: Icon(Icons.more_vert_rounded, size: 18, color: isDark ? Colors.white70 : Colors.black54),
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

          const SizedBox(height: 8),

          // Content Box
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: isDark
                  ? primaryColor.withOpacity(0.08)
                  : theme.colorScheme.surfaceContainerHighest.withOpacity(0.4),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(
                color: isDark
                    ? primaryColor.withOpacity(0.2)
                    : theme.colorScheme.outlineVariant.withOpacity(0.5),
              ),
            ),
            child: Text(
              content.isEmpty ? 'Përmbajtja e shabllonit është bosh.' : content,
              style: TextStyle(
                fontSize: 12.5,
                height: 1.4,
                fontWeight: FontWeight.w500,
                color: isDark ? const Color(0xFFE2E8F0) : theme.colorScheme.onSurfaceVariant,
              ),
            ),
          ),
        ],
      ),
    );
  }
}
