import 'package:flutter/widgets.dart';
import 'role_en.dart';
import 'role_sq.dart';

String roleTr(
  BuildContext context,
  String key, [
  Map<String, String> args = const {},
]) {
  final language = Localizations.localeOf(context).languageCode.toLowerCase();
  final source = language == 'sq' ? roleSq : roleEn;

  var value = source[key] ?? roleEn[key] ?? key;
  args.forEach((name, replacement) {
    value = value.replaceAll('{${name}}', replacement);
  });

  return value;
}