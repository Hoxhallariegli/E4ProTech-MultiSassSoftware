import 'package:flutter/material.dart';
import 'package:mobile_gateway/services/api_service.dart';
import 'package:mobile_gateway/core/widgets/premium_widgets.dart';
import 'package:mobile_gateway/l10n/message_queue_localization.dart';

class MessageQueueCard extends StatelessWidget {
  final Map<String, dynamic> item;
  final VoidCallback? onTap;
  final VoidCallback? onDelete;
  const MessageQueueCard({super.key, required this.item, this.onTap, this.onDelete});

  String get title {
    final value = (item['name'] ?? item['title'] ?? 'ID: ${item['id']}').toString();
    return value.toString().trim().isEmpty ? 'ID: ' + item['id'].toString() : value.toString();
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return PremiumCard(
      onTap: onTap,
      padding: const EdgeInsets.all(14),
      child: Row(children: [
          _Avatar(title: title),
        const SizedBox(width: 14),
        Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text(title, maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontSize: 15.5, fontWeight: FontWeight.w800)),
          const SizedBox(height: 7),
          Wrap(spacing: 6, runSpacing: 6, children: [_InfoChip(label: message_queueTr(context, 'field.booking_id'), value: item['booking']?['name']?.toString() ?? '-'),
_InfoChip(label: message_queueTr(context, 'field.channel'), value: item['channel']?.toString() ?? '-'),
_InfoChip(label: message_queueTr(context, 'field.phone_number'), value: item['phone_number']?.toString() ?? '-'),]),
        ])),
        if (onTap != null || onDelete != null)
          PopupMenuButton<String>(
            onSelected: (value) {
              if (value == 'edit' && onTap != null) onTap!();
              if (value == 'delete' && onDelete != null) onDelete!();
            },
            itemBuilder: (_) => [
              if (onTap != null) PopupMenuItem(value: 'edit', child: Text(message_queueTr(context, 'list.edit'))),
              if (onDelete != null) PopupMenuItem(value: 'delete', child: Text(message_queueTr(context, 'list.delete'))),
            ],
          ),
      ]),
    );
  }
}

class _Avatar extends StatelessWidget {
  final String title;
  const _Avatar({required this.title});
  @override Widget build(BuildContext context) => Container(width: 58, height: 58, decoration: BoxDecoration(gradient: LinearGradient(colors: [Theme.of(context).colorScheme.primaryContainer, Theme.of(context).colorScheme.secondaryContainer]), borderRadius: BorderRadius.circular(17)), child: Center(child: Text(title.isEmpty ? '?' : title.substring(0,1).toUpperCase(), style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w900))));
}

class _InfoChip extends StatelessWidget {
  final String label, value;
  const _InfoChip({required this.label, required this.value});
  @override Widget build(BuildContext context) => Container(padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4), decoration: BoxDecoration(color: Theme.of(context).colorScheme.surfaceContainerHighest, borderRadius: BorderRadius.circular(8)), child: Text('$label: $value', style: TextStyle(fontSize: 10.5, color: Theme.of(context).colorScheme.onSurfaceVariant, fontWeight: FontWeight.w600)));
}