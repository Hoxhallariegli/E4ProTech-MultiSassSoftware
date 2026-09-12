import 'package:flutter/material.dart';
import '../../../../core/widgets/premium_widgets.dart';
import 'module_registry.dart';

class ModulesPage extends StatelessWidget {
  const ModulesPage({super.key});

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final modules = ModuleRegistry.modules;

    if (modules.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.layers_outlined, size: 80, color: theme.colorScheme.primary.withOpacity(0.2)),
            const SizedBox(height: 16),
            const Text(
              'No Modules Found',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 8),
            const Text(
              'Run artisan new:view to generate modules.',
              style: TextStyle(color: Colors.grey),
            ),
          ],
        ),
      );
    }

    return Scaffold(
      backgroundColor: theme.colorScheme.surface,
      appBar: AppBar(
        title: const Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Dynamic Modules', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w900)),
            Text('Generated CRUD views', style: TextStyle(fontSize: 11, color: Colors.grey)),
          ],
        ),
      ),
      body: GridView.builder(
        padding: const EdgeInsets.all(20),
        gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
          crossAxisCount: 2,
          crossAxisSpacing: 16,
          mainAxisSpacing: 16,
          childAspectRatio: 1.1,
        ),
        itemCount: modules.length,
        itemBuilder: (context, index) {
          final module = modules[index];
          return PremiumCard(
            onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => module.page)),
            padding: const EdgeInsets.all(16),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: theme.colorScheme.primary.withOpacity(0.1),
                    shape: BoxShape.circle,
                  ),
                  child: Icon(module.icon, size: 32, color: theme.colorScheme.primary),
                ),
                const SizedBox(height: 12),
                Text(
                  module.name,
                  textAlign: TextAlign.center,
                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}
