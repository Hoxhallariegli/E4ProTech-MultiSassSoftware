import 'package:flutter/widgets.dart';
import 'report_en.dart';
import 'report_sq.dart';

String reportTr(
  BuildContext context,
  String key, [
  Map<String, String> args = const {},
]) {
  final language = Localizations.localeOf(context).languageCode.toLowerCase();

  Map<String, String> getSource() {
    if (language == 'en') return reportEn;
    if (language == 'sq') return reportSq;
    return reportEn;
  }

  final source = getSource();
  var value = source[key] ?? reportEn[key] ?? key;
  args.forEach((name, replacement) {
    value = value.replaceAll('{$name}', replacement);
  });

  return value;
}