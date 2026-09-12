import 'package:flutter/material.dart';

class PremiumButton extends StatelessWidget {
  const PremiumButton({super.key, required this.label, this.onPressed, this.icon, this.loading = false, this.expand = true, this.variant = PremiumButtonVariant.primary});
  final String label;
  final VoidCallback? onPressed;
  final IconData? icon;
  final bool loading;
  final bool expand;
  final PremiumButtonVariant variant;

  @override
  Widget build(BuildContext context) {
    final child = loading
        ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2))
        : Row(mainAxisSize: MainAxisSize.min, mainAxisAlignment: MainAxisAlignment.center, children: [if (icon != null) Icon(icon, size: 19), if (icon != null) const SizedBox(width: 8), Text(label)]);
    final button = switch (variant) {
      PremiumButtonVariant.primary => FilledButton(onPressed: loading ? null : onPressed, child: child),
      PremiumButtonVariant.secondary => OutlinedButton(onPressed: loading ? null : onPressed, child: child),
      PremiumButtonVariant.danger => FilledButton.tonal(onPressed: loading ? null : onPressed, style: FilledButton.styleFrom(foregroundColor: Theme.of(context).colorScheme.error), child: child),
    };
    return expand ? SizedBox(width: double.infinity, child: button) : button;
  }
}

enum PremiumButtonVariant { primary, secondary, danger }
