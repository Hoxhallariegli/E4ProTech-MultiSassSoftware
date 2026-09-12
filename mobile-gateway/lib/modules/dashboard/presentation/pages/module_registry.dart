import 'package:flutter/material.dart';
    import '../../test_module/presentation/pages/test_module_list_page.dart';
    // [REGISTRY_IMPORTS]

class ModuleEntry {
  final String name;
  final IconData icon;
  final Widget page;

  ModuleEntry({required this.name, required this.icon, required this.page});
}

class ModuleRegistry {
  static final List<ModuleEntry> modules = [
    ModuleEntry(name: 'TestModule', icon: Icons.inventory_2_outlined, page: const TestModuleListPage()),
    // [REGISTRY_ENTRIES]
  ];
}
