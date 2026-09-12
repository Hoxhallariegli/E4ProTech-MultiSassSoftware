import 'package:flutter/material.dart';
// [REGISTRY_IMPORTS]

class ModuleEntry {
  final String name;
  final IconData icon;
  final Widget page;
  final String? permission;

  ModuleEntry({
    required this.name,
    required this.icon,
    required this.page,
    this.permission,
  });
}

class ModuleRegistry {
  static final List<ModuleEntry> modules = [// [REGISTRY_ENTRIES]
  ];
}
