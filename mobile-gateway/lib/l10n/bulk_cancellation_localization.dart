import 'package:flutter/widgets.dart';
import 'bulk_cancellation_en.dart';
import 'bulk_cancellation_sq.dart';

String bulk_cancellationTr(
  BuildContext context,
  String key, [
  Map<String, String> args = const {},
]) {
  final language = Localizations.localeOf(context).languageCode.toLowerCase();

  Map<String, String> getSource() {
    if (language == 'en') return bulk_cancellationEn;
    if (language == 'sq') return bulk_cancellationSq;
    return bulk_cancellationEn;
  }

  final source = getSource();
  var value = source[key] ?? bulk_cancellationEn[key] ?? key;
  args.forEach((name, replacement) {
    value = value.replaceAll('{$name}', replacement);
  });

  return value;
}