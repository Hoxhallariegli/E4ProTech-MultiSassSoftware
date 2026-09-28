import 'package:flutter/widgets.dart';
import 'plan_en.dart';
import 'plan_sq.dart';

String planTr(
  BuildContext context,
  String key, [
  Map<String, String> args = const {},
]) {
  final language = Localizations.localeOf(context).languageCode.toLowerCase();

  Map<String, String> getSource() {
    if (language == 'en') return planEn;
    if (language == 'sq') return planSq;
    return planEn;
  }

  final source = getSource();
  var value = source[key] ?? planEn[key] ?? key;
  args.forEach((name, replacement) {
    value = value.replaceAll('{$name}', replacement);
  });

  return value;
}