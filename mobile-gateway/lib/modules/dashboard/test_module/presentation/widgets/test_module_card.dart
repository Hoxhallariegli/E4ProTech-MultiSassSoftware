import 'package:flutter/material.dart';
import '../../../../../services/api_service.dart';
import '../../../../../core/widgets/premium_widgets.dart';
import '../../../../../l10n/test_module_localization.dart';

class TestModuleCard extends StatelessWidget {
  final Map<String, dynamic> item;
  final VoidCallback onTap;
  final VoidCallback onDelete;
  const TestModuleCard({super.key, required this.item, required this.onTap, required this.onDelete});

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
          if (item['image'] != null && item['image'].toString().isNotEmpty)
            ClipRRect(borderRadius: BorderRadius.circular(17), child: Image.network('${ApiService.serverUrl}/${item['image']}', width: 58, height: 58, fit: BoxFit.cover, errorBuilder: (_, __, ___) => _Avatar(title: title)))
          else
            _Avatar(title: title),
        const SizedBox(width: 14),
        Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text(title, maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontSize: 15.5, fontWeight: FontWeight.w800)),
          const SizedBox(height: 7),
          Wrap(spacing: 6, runSpacing: 6, children: [_InfoChip(label: test_moduleTr(context, 'field.name'), value: item['name']?.toString() ?? '-'),
_InfoChip(label: test_moduleTr(context, 'field.description'), value: item['description']?.toString() ?? '-'),
_InfoChip(label: test_moduleTr(context, 'field.qty'), value: item['qty']?.toString() ?? '-'),]),
        ])),
        PopupMenuButton<String>(onSelected: (value) { if (value == 'edit') onTap(); if (value == 'delete') onDelete(); }, itemBuilder: (_) => [PopupMenuItem(value: 'edit', child: Text(test_moduleTr(context, 'list.edit'))), PopupMenuItem(value: 'delete', child: Text(test_moduleTr(context, 'list.delete')))]),
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