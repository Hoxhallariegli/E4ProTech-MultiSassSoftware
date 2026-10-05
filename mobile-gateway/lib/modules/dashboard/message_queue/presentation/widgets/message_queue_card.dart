import 'package:flutter/material.dart';
import 'package:mobile_gateway/core/widgets/premium_widgets.dart';
import 'package:mobile_gateway/l10n/message_queue_localization.dart';

class MessageQueueCard extends StatelessWidget {
  final Map<String, dynamic> item;
  final VoidCallback? onTap;
  final VoidCallback? onDelete;

  const MessageQueueCard({
    super.key,
    required this.item,
    this.onTap,
    this.onDelete,
  });

  String _formatDateTime(String? raw) {
    if (raw == null || raw.trim().isEmpty) return '';
    try {
      final dt = DateTime.parse(raw.trim()).toLocal();
      final now = DateTime.now();

      final hour = dt.hour.toString().padLeft(2, '0');
      final minute = dt.minute.toString().padLeft(2, '0');
      final timeStr = '$hour:$minute';

      final isToday = dt.year == now.year && dt.month == now.month && dt.day == now.day;
      final tomorrow = now.add(const Duration(days: 1));
      final isTomorrow = dt.year == tomorrow.year && dt.month == tomorrow.month && dt.day == tomorrow.day;
      final yesterday = now.subtract(const Duration(days: 1));
      final isYesterday = dt.year == yesterday.year && dt.month == yesterday.month && dt.day == yesterday.day;

      if (isToday) {
        return 'Sot në $timeStr';
      } else if (isTomorrow) {
        return 'Nesër në $timeStr';
      } else if (isYesterday) {
        return 'Dje në $timeStr';
      } else {
        final day = dt.day.toString().padLeft(2, '0');
        final month = dt.month.toString().padLeft(2, '0');
        final year = dt.year;
        return '$day/$month/$year $timeStr';
      }
    } catch (_) {
      final clean = raw.replaceAll('Z', '').replaceAll('T', ' ');
      final parts = clean.split(' ');
      if (parts.length >= 2) {
        final datePart = parts[0];
        final timeParts = parts[1].split(':');
        if (timeParts.length >= 2) {
          return '$datePart ${timeParts[0]}:${timeParts[1]}';
        }
      }
      return raw;
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    final primaryColor = theme.colorScheme.primary;

    final customerName = (item['customer_name'] ?? item['booking']?['customer']?['name'] ?? item['phone_number'] ?? 'Radhë Mesazhi').toString();
    final phone = (item['phone_number'] ?? '').toString();
    final channel = (item['channel'] ?? 'sms').toString().toLowerCase();
    final message = (item['message_content'] ?? '').toString();
    final status = (item['status'] ?? 'pending').toString().toLowerCase();
    final scheduledAtRaw = item['scheduled_at']?.toString() ?? item['created_at']?.toString();
    final updatedAtRaw = item['updated_at']?.toString();
    final retryCount = int.tryParse(item['retry_count']?.toString() ?? '0') ?? 0;

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

    Color statusColor = Colors.amber.shade700;
    String statusLabel = 'PENDING';
    if (status == 'processing') {
      statusColor = primaryColor;
      statusLabel = 'PROCESSING';
    } else if (status == 'sent') {
      statusColor = Colors.green.shade600;
      statusLabel = 'SENT';
    } else if (status == 'failed') {
      statusColor = Colors.red.shade600;
      statusLabel = 'FAILED';
    } else if (status == 'skipped_limit') {
      statusColor = Colors.purple.shade600;
      statusLabel = 'SKIPPED';
    }

    return PremiumCard(
      onTap: onTap,
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 11),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Row 1: Recipient + Badges + Popup Menu
          Row(
            children: [
              Icon(channelIcon, size: 16, color: channelColor),
              const SizedBox(width: 8),
              Expanded(
                child: Row(
                  children: [
                    Flexible(
                      child: Text(
                        customerName,
                        style: TextStyle(
                          fontSize: 15,
                          fontWeight: FontWeight.w900,
                          color: isDark ? Colors.white : theme.colorScheme.onSurface,
                        ),
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                    if (phone.isNotEmpty && phone != customerName) ...[
                      const SizedBox(width: 6),
                      Text(
                        '($phone)',
                        style: TextStyle(
                          fontSize: 11,
                          color: isDark ? Colors.grey.shade400 : Colors.grey.shade600,
                          fontWeight: FontWeight.w600,
                        ),
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                  ],
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

              // Status Badge
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                decoration: BoxDecoration(
                  color: statusColor.withOpacity(0.12),
                  borderRadius: BorderRadius.circular(6),
                  border: Border.all(color: statusColor.withOpacity(0.3)),
                ),
                child: Text(
                  statusLabel,
                  style: TextStyle(
                    fontSize: 9.5,
                    fontWeight: FontWeight.bold,
                    color: statusColor,
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
                    if (onTap != null) PopupMenuItem(value: 'edit', child: Text(message_queueTr(context, 'list.edit'))),
                    if (onDelete != null) PopupMenuItem(value: 'delete', child: Text(message_queueTr(context, 'list.delete'))),
                  ],
                ),
            ],
          ),

          const SizedBox(height: 6),

          // Message Content Container (FULL MESSAGE DISPLAY)
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
              message.isEmpty ? 'Përmbajtja e mesazhit nuk ekziston.' : message,
              style: TextStyle(
                fontSize: 12.5,
                height: 1.4,
                fontWeight: FontWeight.w500,
                color: isDark ? const Color(0xFFE2E8F0) : theme.colorScheme.onSurfaceVariant,
              ),
            ),
          ),

          const SizedBox(height: 8),

          // Footer Row: Timestamps (Scheduled At & Sent At)
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              if (scheduledAtRaw != null && scheduledAtRaw.isNotEmpty)
                Row(
                  children: [
                    Icon(Icons.schedule_rounded, size: 11, color: isDark ? Colors.white70 : Colors.black54),
                    const SizedBox(width: 4),
                    Text(
                      'Planifikuar: ${_formatDateTime(scheduledAtRaw)}',
                      style: TextStyle(
                        fontSize: 10.5,
                        fontWeight: FontWeight.bold,
                        color: isDark ? Colors.white70 : Colors.black87,
                      ),
                    ),
                  ],
                )
              else
                const SizedBox.shrink(),

              Row(
                children: [
                  Icon(
                    status == 'sent' ? Icons.check_circle_rounded : Icons.access_time_rounded,
                    size: 11,
                    color: status == 'sent' ? Colors.green : primaryColor,
                  ),
                  const SizedBox(width: 4),
                  Text(
                    status == 'sent' && updatedAtRaw != null
                        ? 'Dërguar: ${_formatDateTime(updatedAtRaw)}'
                        : (status == 'failed' ? 'Dështoi' : 'Në Pritje'),
                    style: TextStyle(
                      fontSize: 10.5,
                      fontWeight: FontWeight.bold,
                      color: status == 'sent' ? Colors.green : primaryColor,
                    ),
                  ),
                  if (retryCount > 0) ...[
                    const SizedBox(width: 6),
                    Text(
                      '($retryCount)',
                      style: const TextStyle(fontSize: 10, color: Colors.orange, fontWeight: FontWeight.bold),
                    ),
                  ],
                ],
              ),
            ],
          ),
        ],
      ),
    );
  }
}
