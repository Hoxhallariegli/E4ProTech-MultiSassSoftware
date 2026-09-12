import 'package:flutter/widgets.dart';
import 'test_module_en.dart';
import 'test_module_sq.dart';

String test_moduleTr(
  BuildContext context,
  String key, [
  Map<String, String> args = const {},
]) {
  final language = Localizations.localeOf(context).languageCode.toLowerCase();
  final source = language == 'sq' ? test_moduleSq : test_moduleEn;

  var value = source[key] ?? test_moduleEn[key] ?? key;
  args.forEach((name, replacement) {
    value = value.replaceAll('{$name}', replacement);
  });

  return value;
}