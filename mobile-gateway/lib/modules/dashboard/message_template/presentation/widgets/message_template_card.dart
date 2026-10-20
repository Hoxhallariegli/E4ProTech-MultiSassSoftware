import 'package:flutter/material.dart';
import 'package:mobile_gateway/core/widgets/premium_widgets.dart';
import 'package:mobile_gateway/l10n/message_template_localization.dart';

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

    final String contentSq = (item['content_sq'] ?? (item['content'] is Map ? item['content']['sq'] : item['content']) ?? '').toString();
    final String contentEn = (item['content_en'] ?? (item['content'] is Map ? item['content']['en'] : '') ?? '').toString();

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
    String typeLabel = message_templateTr(context, 'type.confirmation');
    if (type == 'reminder') {
      typeColor = Colors.amber.shade700;
      typeLabel = message_templateTr(context, 'type.reminder');
    } else if (type == 'welcome') {
      typeColor = Colors.green.shade600;
      typeLabel = message_templateTr(context, 'type.welcome');
    } else if (type == 'reschedule' || type == 'update') {
      typeColor = Colors.blue.shade600;
      typeLabel = message_templateTr(context, 'type.reschedule');
    } else if (type == 'cancellation' || type == 'cancelled') {
      typeColor = Colors.red.shade600;
      typeLabel = message_templateTr(context, 'type.cancellation');
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
                    if (onTap != null) PopupMenuItem(value: 'edit', child: Text('✏️ ${message_templateTr(context, 'list.edit')}')),
                    if (onDelete != null) PopupMenuItem(value: 'delete', child: Text('🗑️ ${message_templateTr(context, 'list.delete')}')),
                  ],
                ),
            ],
          ),

          const SizedBox(height: 8),

          // Content Box - Shqip (SQ)
          if (contentSq.isNotEmpty) ...[
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
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(message_templateTr(context, 'card.albanian'), style: const TextStyle(fontSize: 10.5, fontWeight: FontWeight.bold, color: Colors.grey)),
                  const SizedBox(height: 3),
                  Text(
                    contentSq,
                    style: TextStyle(
                      fontSize: 12.5,
                      height: 1.35,
                      fontWeight: FontWeight.w500,
                      color: isDark ? const Color(0xFFE2E8F0) : theme.colorScheme.onSurfaceVariant,
                    ),
                  ),
                ],
              ),
            ),
          ],

          if (contentSq.isNotEmpty && contentEn.isNotEmpty) const SizedBox(height: 6),

          // Content Box - English (EN)
          if (contentEn.isNotEmpty) ...[
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: isDark
                    ? theme.colorScheme.surfaceContainerHighest.withOpacity(0.2)
                    : theme.colorScheme.surfaceContainerHighest.withOpacity(0.25),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(
                  color: theme.colorScheme.outlineVariant.withOpacity(0.4),
                ),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(message_templateTr(context, 'card.english'), style: const TextStyle(fontSize: 10.5, fontWeight: FontWeight.bold, color: Colors.grey)),
                  const SizedBox(height: 3),
                  Text(
                    contentEn,
                    style: TextStyle(
                      fontSize: 12.5,
                      height: 1.35,
                      fontWeight: FontWeight.w500,
                      color: isDark ? const Color(0xFFE2E8F0) : theme.colorScheme.onSurfaceVariant,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }
}
