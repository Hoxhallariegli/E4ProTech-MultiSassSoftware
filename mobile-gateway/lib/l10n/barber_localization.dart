import 'package:flutter/widgets.dart';
import 'package:mobile_gateway/core/branding/branding_cubit.dart';
import 'barber_en.dart';
import 'barber_sq.dart';

String barberTr(
  BuildContext context,
  String key, [
  Map<String, String> args = const {},
]) {
  final language = Localizations.localeOf(context).languageCode.toLowerCase();

  Map<String, String> getSource() {
    if (language == 'en') return barberEn;
    if (language == 'sq') return barberSq;
    return barberEn;
  }

  final source = getSource();
  var value = source[key] ?? barberEn[key] ?? key;
  args.forEach((name, replacement) {
    value = value.replaceAll('{$name}', replacement);
  });

  return applyDynamicBranding(context, value);
}
