import 'package:flutter/material.dart';

class PremiumPicker<T> extends StatelessWidget {
  const PremiumPicker({super.key, required this.label, this.valueLabel, required this.options, required this.onSelected, this.icon = Icons.keyboard_arrow_down_rounded, this.enabled = true, this.itemBuilder});
  final String label;
  final String? valueLabel;
  final List<T> options;
  final ValueChanged<T> onSelected;
  final IconData icon;
  final bool enabled;
  final Widget Function(BuildContext, T)? itemBuilder;

  @override
  Widget build(BuildContext context) => InkWell(
        onTap: !enabled ? null : () => showModalBottomSheet<void>(
          context: context,
          isScrollControlled: true,
          builder: (_) => SafeArea(child: Padding(padding: const EdgeInsets.fromLTRB(20, 12, 20, 20), child: Column(mainAxisSize: MainAxisSize.min, children: [
            Container(width: 38, height: 4, decoration: BoxDecoration(color: Theme.of(context).colorScheme.outlineVariant, borderRadius: BorderRadius.circular(99))),
            const SizedBox(height: 18),
            Align(alignment: Alignment.centerLeft, child: Text(label, style: Theme.of(context).textTheme.titleLarge)),
            const SizedBox(height: 10),
            Flexible(child: ListView.separated(shrinkWrap: true, itemCount: options.length, separatorBuilder: (_, __) => const SizedBox(height: 4), itemBuilder: (context, index) => ListTile(shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)), title: itemBuilder?.call(context, options[index]) ?? Text(options[index].toString()), onTap: () { Navigator.pop(context); onSelected(options[index]); }))),
          ]))),
        ),
        borderRadius: BorderRadius.circular(14),
        child: InputDecorator(
          decoration: InputDecoration(labelText: label, suffixIcon: Icon(icon), enabled: enabled),
          child: Text(valueLabel?.isNotEmpty == true ? valueLabel! : 'Select...', style: Theme.of(context).textTheme.bodyLarge),
        ),
      );
}
