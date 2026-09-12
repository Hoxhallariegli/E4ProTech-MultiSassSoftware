import 'package:flutter/material.dart';

class PremiumFilterSheet extends StatelessWidget {
  const PremiumFilterSheet({super.key, required this.title, required this.child, this.onClear, this.onApply, this.applyLabel = 'Apply filters'});
  final String title;
  final Widget child;
  final VoidCallback? onClear;
  final VoidCallback? onApply;
  final String applyLabel;

  static Future<void> show(BuildContext context, {required String title, required Widget child, VoidCallback? onClear, VoidCallback? onApply}) => showModalBottomSheet<void>(context: context, isScrollControlled: true, builder: (_) => PremiumFilterSheet(title: title, child: child, onClear: onClear, onApply: onApply));

  @override Widget build(BuildContext context) => SafeArea(child: Padding(padding: EdgeInsets.fromLTRB(20, 12, 20, 20 + MediaQuery.viewInsetsOf(context).bottom), child: Column(mainAxisSize: MainAxisSize.min, crossAxisAlignment: CrossAxisAlignment.stretch, children: [Center(child: Container(width: 38, height: 4, decoration: BoxDecoration(color: Theme.of(context).colorScheme.outlineVariant, borderRadius: BorderRadius.circular(99)))), const SizedBox(height: 18), Row(children: [Expanded(child: Text(title, style: Theme.of(context).textTheme.titleLarge)), if (onClear != null) TextButton(onPressed: onClear, child: const Text('Clear'))]), const SizedBox(height: 12), child, const SizedBox(height: 20), FilledButton(onPressed: onApply ?? () => Navigator.pop(context), child: Text(applyLabel))])));
}
