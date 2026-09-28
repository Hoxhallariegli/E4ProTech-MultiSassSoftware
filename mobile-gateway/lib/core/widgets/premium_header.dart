import 'package:flutter/material.dart';
import '../../services/auth_service.dart';
import '../../modules/dashboard/presentation/widgets/shop_switcher_widget.dart';

class PremiumHeader extends StatelessWidget implements PreferredSizeWidget {
  final String title;
  final List<Widget>? actions;
  final Widget? leading;
  final VoidCallback? onShopSwitched;

  const PremiumHeader({
    super.key,
    required this.title,
    this.actions,
    this.leading,
    this.onShopSwitched,
  });

  @override
  Size get preferredSize => const Size.fromHeight(kToolbarHeight + 8);

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final businessName = AuthService.instance.user?['business']?['name'] ?? 'Zgjidh Dyqanin';

    return AppBar(
      leading: leading,
      elevation: 0,
      titleSpacing: leading == null ? 20 : 0,
      title: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Text(
            title,
            style: const TextStyle(fontSize: 19, fontWeight: FontWeight.w900),
          ),
          const SizedBox(height: 2),
          InkWell(
            onTap: () {
              showModalBottomSheet(
                context: context,
                isScrollControlled: true,
                backgroundColor: Colors.transparent,
                builder: (_) => ShopSwitcherWidget(
                  onSwitched: () {
                    if (onShopSwitched != null) {
                      onShopSwitched!();
                    }
                  },
                ),
              );
            },
            borderRadius: BorderRadius.circular(4),
            child: Padding(
              padding: const EdgeInsets.symmetric(vertical: 2),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(Icons.storefront_rounded, size: 12, color: theme.colorScheme.primary),
                  const SizedBox(width: 4),
                  Text(
                    businessName,
                    style: TextStyle(
                      fontSize: 11,
                      color: theme.colorScheme.primary,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(width: 2),
                  Icon(Icons.keyboard_arrow_down_rounded, size: 12, color: theme.colorScheme.primary),
                ],
              ),
            ),
          ),
        ],
      ),
      actions: actions,
    );
  }
}
