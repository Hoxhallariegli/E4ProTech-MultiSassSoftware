import 'package:flutter/widgets.dart';
import 'test-module_en.dart';
import 'test-module_sq.dart';

String testModuleTr(
  BuildContext context,
  String key, [
  Map<String, String> args = const {},
]) {
  final language = Localizations.localeOf(context).languageCode.toLowerCase();
  final source = language == 'sq' ? testModuleSq : testModuleEn;

  var value = source[key] ?? testModuleEn[key] ?? key;
  args.forEach((name, replacement) {
    value = value.replaceAll('{$name}', replacement);
  });

  return value;
}
