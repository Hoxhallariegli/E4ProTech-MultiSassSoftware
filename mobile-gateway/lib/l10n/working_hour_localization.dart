import 'package:flutter/widgets.dart';
import 'package:mobile_gateway/core/branding/branding_cubit.dart';
import 'working_hour_en.dart';
import 'working_hour_sq.dart';

String working_hourTr(
  BuildContext context,
  String key, [
  Map<String, String> args = const {},
]) {
  final language = Localizations.localeOf(context).languageCode.toLowerCase();

  Map<String, String> getSource() {
    if (language == 'en') return working_hourEn;
    if (language == 'sq') return working_hourSq;
    return working_hourEn;
  }

  final source = getSource();
  var value = source[key] ?? working_hourEn[key] ?? key;
  args.forEach((name, replacement) {
    value = value.replaceAll('{$name}', replacement);
  });

  return applyDynamicBranding(context, value);
}
