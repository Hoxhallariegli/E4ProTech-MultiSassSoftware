import 'package:flutter/widgets.dart';
import 'audit_en.dart';
import 'audit_sq.dart';

String auditTr(
  BuildContext context,
  String key, [
  Map<String, String> args = const {},
]) {
  final language = Localizations.localeOf(context).languageCode.toLowerCase();
  final source = language == 'sq' ? auditSq : auditEn;

  var value = source[key] ?? auditEn[key] ?? key;
  args.forEach((name, replacement) {
    value = value.replaceAll('{${name}}', replacement);
  });

  return value;
}