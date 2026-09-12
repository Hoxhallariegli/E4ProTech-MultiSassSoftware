import 'package:flutter/material.dart';

class PremiumAppBar extends StatelessWidget implements PreferredSizeWidget {
  const PremiumAppBar({super.key, required this.title, this.subtitle, this.actions, this.leading});
  final String title;
  final String? subtitle;
  final List<Widget>? actions;
  final Widget? leading;
  @override Size get preferredSize => const Size.fromHeight(kToolbarHeight + 8);
  @override Widget build(BuildContext context) => AppBar(leading: leading, titleSpacing: leading == null ? 20 : 0, title: Column(crossAxisAlignment: CrossAxisAlignment.start, mainAxisAlignment: MainAxisAlignment.center, children: [Text(title), if (subtitle != null) Text(subtitle!, style: Theme.of(context).textTheme.labelSmall?.copyWith(color: Theme.of(context).colorScheme.onSurfaceVariant))]), actions: actions);
}
