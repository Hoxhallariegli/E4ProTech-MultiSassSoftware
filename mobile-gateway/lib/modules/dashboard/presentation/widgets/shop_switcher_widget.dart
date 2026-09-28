import 'package:flutter/material.dart';
import '../../../../services/auth_service.dart';
import '../../../../services/api_service.dart';
import '../../../../core/theme/app_theme.dart';

class ShopSwitcherWidget extends StatefulWidget {
  final VoidCallback onSwitched;
  const ShopSwitcherWidget({super.key, required this.onSwitched});

  @override
  State<ShopSwitcherWidget> createState() => _ShopSwitcherWidgetState();
}

class _ShopSwitcherWidgetState extends State<ShopSwitcherWidget> {
  bool _loading = false;

  void _handleSwitch(int shopId) async {
    setState(() => _loading = true);
    try {
      await AuthService.instance.switchShop(shopId);
      widget.onSwitched();
      if (mounted) Navigator.pop(context);
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gabim: $e'), backgroundColor: Colors.redAccent),
        );
      }
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final shops = AuthService.instance.accessibleShops;
    final currentId = AuthService.instance.currentBarberShopId;

    return SafeArea(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 20),
        decoration: BoxDecoration(
          color: theme.colorScheme.surface,
          borderRadius: const BorderRadius.vertical(top: Radius.circular(28)),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 8),
              child: Text(
                'NDËRRO DYQANIN',
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w900,
                  letterSpacing: 1.2,
                  color: theme.colorScheme.primary,
                ),
              ),
            ),
            const SizedBox(height: 8),
            if (_loading)
              const Center(child: Padding(padding: EdgeInsets.all(20), child: CircularProgressIndicator()))
            else
              ...shops.map((shop) {
                final id = shop['id'] as int;
                final isSelected = id == currentId;
                return ListTile(
                  onTap: isSelected ? null : () => _handleSwitch(id),
                  contentPadding: const EdgeInsets.symmetric(horizontal: 24, vertical: 4),
                  leading: Container(
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: isSelected ? theme.colorScheme.primary : theme.colorScheme.surfaceVariant,
                      shape: BoxShape.circle,
                    ),
                    child: Icon(
                      Icons.storefront_rounded,
                      color: isSelected ? Colors.white : Colors.grey,
                      size: 20,
                    ),
                  ),
                  title: Text(
                    shop['name'] ?? 'Dyqan pa emër',
                    style: TextStyle(
                      fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
                      color: isSelected ? theme.colorScheme.primary : theme.colorScheme.onSurface,
                    ),
                  ),
                  trailing: isSelected
                      ? Icon(Icons.check_circle, color: theme.colorScheme.primary, size: 20)
                      : null,
                );
              }),
            const SizedBox(height: 20),
          ],
        ),
      ),
    );
  }
}
