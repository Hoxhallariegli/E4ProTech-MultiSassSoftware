import 'package:flutter/widgets.dart';
import 'admin_en.dart';
import 'admin_sq.dart';

String adminTr(
  BuildContext context,
  String key, [
  Map<String, String> args = const {},
]) {
  final language = Localizations.localeOf(context).languageCode.toLowerCase();
  final source = language == 'sq' ? adminSq : adminEn;

  var value = source[key] ?? adminEn[key] ?? key;
  args.forEach((name, replacement) {
    value = value.replaceAll('{${name}}', replacement);
  });

  return value;
}