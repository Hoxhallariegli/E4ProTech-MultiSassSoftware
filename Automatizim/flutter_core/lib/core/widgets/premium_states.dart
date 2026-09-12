import 'package:flutter/material.dart';

class PremiumEmptyState extends StatelessWidget {
  const PremiumEmptyState({super.key, this.title = 'Nothing here', this.message, this.icon = Icons.inbox_outlined, this.action, this.actionLabel});
  final String title;
  final String? message;
  final IconData icon;
  final VoidCallback? action;
  final String? actionLabel;
  @override Widget build(BuildContext context) => Center(child: Padding(padding: const EdgeInsets.all(32), child: Column(mainAxisSize: MainAxisSize.min, children: [Icon(icon, size: 48, color: Theme.of(context).colorScheme.outline), const SizedBox(height: 14), Text(title, style: Theme.of(context).textTheme.titleMedium), if (message != null) ...[const SizedBox(height: 6), Text(message!, textAlign: TextAlign.center)], if (action != null && actionLabel != null) ...[const SizedBox(height: 16), FilledButton(onPressed: action, child: Text(actionLabel!))]])));
}

class PremiumErrorState extends StatelessWidget {
  const PremiumErrorState({super.key, this.title = 'Something went wrong', this.message, this.onRetry});
  final String title;
  final String? message;
  final VoidCallback? onRetry;
  @override Widget build(BuildContext context) => Center(child: Padding(padding: const EdgeInsets.all(32), child: Column(mainAxisSize: MainAxisSize.min, children: [Icon(Icons.error_outline_rounded, size: 48, color: Theme.of(context).colorScheme.error), const SizedBox(height: 14), Text(title, style: Theme.of(context).textTheme.titleMedium), if (message != null) ...[const SizedBox(height: 6), Text(message!, textAlign: TextAlign.center)], if (onRetry != null) ...[const SizedBox(height: 16), OutlinedButton.icon(onPressed: onRetry, icon: const Icon(Icons.refresh_rounded), label: const Text('Retry'))]])));
}

class PremiumSkeleton extends StatelessWidget {
  const PremiumSkeleton({super.key, this.height = 92, this.borderRadius = 18, this.margin});
  final double height;
  final double borderRadius;
  final EdgeInsetsGeometry? margin;
  @override Widget build(BuildContext context) => Container(margin: margin, height: height, decoration: BoxDecoration(color: Theme.of(context).colorScheme.surfaceContainerHighest, borderRadius: BorderRadius.circular(borderRadius)));
}
