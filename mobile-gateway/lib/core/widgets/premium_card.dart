import 'package:flutter/material.dart';
import '../theme/app_radius.dart';

class PremiumCard extends StatelessWidget {
  const PremiumCard({super.key, required this.child, this.onTap, this.padding = const EdgeInsets.all(16), this.margin, this.color, this.border, this.leading, this.trailing});
  final Widget child;
  final VoidCallback? onTap;
  final EdgeInsetsGeometry padding;
  final EdgeInsetsGeometry? margin;
  final Color? color;
  final Border? border;
  final Widget? leading;
  final Widget? trailing;

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    final content = Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
      if (leading != null) ...[leading!, const SizedBox(width: 12)],
      Expanded(child: child),
      if (trailing != null) ...[const SizedBox(width: 12), trailing!],
    ]);
    return Container(
      margin: margin,
      decoration: BoxDecoration(
        color: color ?? scheme.surface,
        borderRadius: BorderRadius.circular(AppRadius.lg),
        border: border ?? Border.all(color: scheme.outlineVariant.withValues(alpha: .65)),
        boxShadow: [BoxShadow(color: scheme.shadow.withValues(alpha: .06), blurRadius: 24, offset: const Offset(0, 8))],
      ),
      child: Material(color: Colors.transparent, child: InkWell(onTap: onTap, borderRadius: BorderRadius.circular(AppRadius.lg), child: Padding(padding: padding, child: content))),
    );
  }
}
