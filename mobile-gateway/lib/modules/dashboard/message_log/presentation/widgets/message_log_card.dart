import 'package:flutter/material.dart';
import 'package:mobile_gateway/core/widgets/premium_widgets.dart';

class MessageLogCard extends StatelessWidget {
  final Map<String, dynamic> item;
  final VoidCallback? onTap;
  final VoidCallback? onDelete;

  const MessageLogCard({
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
      final yesterday = now.subtract(const Duration(days: 1));
      final isYesterday = dt.year == yesterday.year && dt.month == yesterday.month && dt.day == yesterday.day;

      if (isToday) {
        return 'Sot në $timeStr';
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

    final customerName = (item['customer_name'] ?? item['customer']?['name'] ?? 'Klient').toString();
    final customerPhone = (item['customer_phone'] ?? item['customer']?['phone'] ?? '').toString();
    final channel = (item['channel'] ?? 'sms').toString().toLowerCase();
    final message = (item['message'] ?? '').toString();
    final status = (item['status'] ?? 'pending').toString().toLowerCase();

    final appointmentAtRaw = item['appointment_at']?.toString();
    final createdAtRaw = item['created_at']?.toString() ?? item['scheduled_at']?.toString();
    final sentAtRaw = item['sent_at']?.toString();
    final errorMsg = item['error_message']?.toString() ?? '';

    final templateType = (item['template_type'] ?? 'confirmation').toString().toLowerCase();
    final templateTypeLabel = (item['template_type_label'] ?? (templateType == 'reminder' ? 'Rikujtesë' : (templateType == 'welcome' ? 'Mirëseardhje' : 'Konfirmim'))).toString();

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
    if (templateType == 'reminder') {
      typeColor = Colors.amber.shade700;
    } else if (templateType == 'welcome') {
      typeColor = Colors.green.shade600;
    }

    Color statusColor = Colors.amber.shade700;
    String statusLabel = 'PENDING';
    if (status == 'sent') {
      statusColor = Colors.green.shade600;
      statusLabel = 'SENT';
    } else if (status == 'failed') {
      statusColor = Colors.red.shade600;
      statusLabel = 'FAILED';
    }

    return PremiumCard(
      onTap: onTap,
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 11),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Row 1: Customer Info & Badges
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
                    if (customerPhone.isNotEmpty) ...[
                      const SizedBox(width: 6),
                      Text(
                        '($customerPhone)',
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

              // Template Type Badge
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                decoration: BoxDecoration(
                  color: typeColor.withOpacity(0.12),
                  borderRadius: BorderRadius.circular(6),
                  border: Border.all(color: typeColor.withOpacity(0.3)),
                ),
                child: Text(
                  templateTypeLabel.toUpperCase(),
                  style: TextStyle(
                    fontSize: 9.5,
                    fontWeight: FontWeight.bold,
                    color: typeColor,
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

              if (onDelete != null)
                PopupMenuButton<String>(
                  padding: EdgeInsets.zero,
                  icon: Icon(Icons.more_vert_rounded, size: 18, color: isDark ? Colors.white70 : Colors.black54),
                  onSelected: (value) {
                    if (value == 'delete' && onDelete != null) onDelete!();
                  },
                  itemBuilder: (_) => [
                    if (onDelete != null) const PopupMenuItem(value: 'delete', child: Text('🗑️ Fshi Log-un')),
                  ],
                ),
            ],
          ),

          const SizedBox(height: 6),

          // Message Content Container (FULL TEXT DISPLAY)
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
                height: 1.35,
                fontWeight: FontWeight.w500,
                color: isDark ? const Color(0xFFE2E8F0) : theme.colorScheme.onSurfaceVariant,
              ),
            ),
          ),

          if (errorMsg.isNotEmpty) ...[
            const SizedBox(height: 4),
            Text(
              'Gabim: $errorMsg',
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(fontSize: 10.5, color: Colors.red, fontWeight: FontWeight.bold),
            ),
          ],

          const SizedBox(height: 8),

          // Footer Row: Full Booking & Delivery Timeline
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: isDark ? Colors.black26 : Colors.grey.shade50,
              borderRadius: BorderRadius.circular(10),
            ),
            child: Column(
              children: [
                if (appointmentAtRaw != null && appointmentAtRaw.isNotEmpty)
                  Row(
                    children: [
                      Icon(Icons.event_available_rounded, size: 11, color: primaryColor),
                      const SizedBox(width: 4),
                      Text(
                        'Takimi Për: ${_formatDateTime(appointmentAtRaw)}',
                        style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.w800, color: primaryColor),
                      ),
                    ],
                  ),
                if (appointmentAtRaw != null && appointmentAtRaw.isNotEmpty) const SizedBox(height: 4),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    if (createdAtRaw != null && createdAtRaw.isNotEmpty)
                      Row(
                        children: [
                          Icon(Icons.schedule_rounded, size: 11, color: isDark ? Colors.white70 : Colors.black54),
                          const SizedBox(width: 4),
                          Text(
                            'Krijuar: ${_formatDateTime(createdAtRaw)}',
                            style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: isDark ? Colors.white70 : Colors.black87),
                          ),
                        ],
                      ),
                    Row(
                      children: [
                        Icon(
                          status == 'sent' ? Icons.check_circle_rounded : Icons.access_time_rounded,
                          size: 11,
                          color: status == 'sent' ? Colors.green : Colors.amber.shade800,
                        ),
                        const SizedBox(width: 4),
                        Text(
                          status == 'sent' && sentAtRaw != null
                              ? 'Dërguar: ${_formatDateTime(sentAtRaw)}'
                              : (status == 'failed' ? 'Dështoi' : 'Në Pritje'),
                          style: TextStyle(
                            fontSize: 10,
                            fontWeight: FontWeight.bold,
                            color: status == 'sent' ? Colors.green : Colors.amber.shade800,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
