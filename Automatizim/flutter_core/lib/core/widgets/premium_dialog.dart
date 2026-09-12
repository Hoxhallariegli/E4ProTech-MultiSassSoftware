import 'package:flutter/material.dart';

class PremiumDialog {
  static Future<bool> confirm(BuildContext context, {required String title, required String message, String confirmLabel = 'Confirm', String cancelLabel = 'Cancel', bool destructive = false}) async {
    final result = await showDialog<bool>(context: context, builder: (context) => AlertDialog(title: Text(title), content: Text(message), actions: [TextButton(onPressed: () => Navigator.pop(context, false), child: Text(cancelLabel)), FilledButton.tonal(onPressed: () => Navigator.pop(context, true), style: destructive ? FilledButton.styleFrom(foregroundColor: Theme.of(context).colorScheme.error) : null, child: Text(confirmLabel))]));
    return result ?? false;
  }
}
