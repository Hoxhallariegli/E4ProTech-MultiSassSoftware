import 'package:flutter/material.dart';
import 'package:mobile_gateway/core/widgets/premium_widgets.dart';
import 'package:mobile_gateway/l10n/message_log_localization.dart';

class MessageLogCard extends StatelessWidget {
  final Map<String, dynamic> item;
  final VoidCallback? onTap;
  final VoidCallback? onDelete;
  const MessageLogCard({super.key, required this.item, this.onTap, this.onDelete});

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;

    final customer = (item['customer_name'] ?? item['customer']?['name'] ?? 'Klient').toString();
    final channel = (item['channel'] ?? 'whatsapp').toString().toLowerCase();
    final message = (item['message'] ?? '').toString();
    final status = (item['status'] ?? 'sent').toString().toLowerCase();
    final sentAt = item['sent_at']?.toString() ?? item['created_at']?.toString();

    IconData channelIcon = Icons.chat_bubble_outline_rounded;
    Color channelColor = Colors.green;
    if (channel == 'sms') {
      channelIcon = Icons.sms_outlined;
      channelColor = Colors.blue;
    } else if (channel == 'email') {
      channelIcon = Icons.email_outlined;
      channelColor = Colors.amber;
    }

    return PremiumCard(
      onTap: onTap,
      padding: const EdgeInsets.all(14),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 48,
            height: 48,
            decoration: BoxDecoration(
              color: channelColor.withOpacity(0.12),
              borderRadius: BorderRadius.circular(16),
            ),
            child: Icon(channelIcon, color: channelColor, size: 22),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Expanded(
                      child: Text(
                        customer,
                        style: TextStyle(
                          fontSize: 15.5,
                          fontWeight: FontWeight.w900,
                          color: isDark ? Colors.white : theme.colorScheme.onSurface,
                        ),
                      ),
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                      decoration: BoxDecoration(
                        color: status == 'sent' ? Colors.green.withOpacity(0.12) : Colors.red.withOpacity(0.12),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Text(
                        status.toUpperCase(),
                        style: TextStyle(
                          fontSize: 9.5,
                          fontWeight: FontWeight.bold,
                          color: status == 'sent' ? Colors.green : Colors.red,
                        ),
                      ),
                    ),
                  ],
                ),
                if (message.isNotEmpty) ...[
                  const SizedBox(height: 4),
                  Text(
                    message,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: TextStyle(
                      fontSize: 12.5,
                      color: isDark ? const Color(0xFFCBD5E1) : theme.colorScheme.onSurfaceVariant,
                    ),
                  ),
                ],
                if (sentAt != null && sentAt.isNotEmpty) ...[
                  const SizedBox(height: 4),
                  Text(
                    sentAt,
                    style: const TextStyle(fontSize: 10.5, color: Colors.grey),
                  ),
                ],
              ],
            ),
          ),
          PopupMenuButton<String>(
            icon: Icon(Icons.more_vert_rounded, color: isDark ? Colors.white70 : Colors.black54),
            onSelected: (value) {
              if (value == 'view' && onTap != null) onTap!();
              if (value == 'edit' && onTap != null) onTap!();
              if (value == 'delete' && onDelete != null) onDelete!();
            },
            itemBuilder: (_) => [
              if (onTap != null) const PopupMenuItem(value: 'view', child: Text('👁️ Shiko Detajet')),
              if (onDelete != null) PopupMenuItem(value: 'delete', child: Text(message_logTr(context, 'list.delete'))),
            ],
          ),
        ],
      ),
    );
  }
}
