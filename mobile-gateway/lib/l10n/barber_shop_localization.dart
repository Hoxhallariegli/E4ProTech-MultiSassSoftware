import 'package:flutter/widgets.dart';
import 'package:mobile_gateway/core/branding/branding_cubit.dart';
import 'barber_shop_en.dart';
import 'barber_shop_sq.dart';

String barber_shopTr(
  BuildContext context,
  String key, [
  Map<String, String> args = const {},
]) {
  final language = Localizations.localeOf(context).languageCode.toLowerCase();

  Map<String, String> getSource() {
    if (language == 'en') return barber_shopEn;
    if (language == 'sq') return barber_shopSq;
    return barber_shopEn;
  }

  final source = getSource();
  var value = source[key] ?? barber_shopEn[key] ?? key;
  args.forEach((name, replacement) {
    value = value.replaceAll('{$name}', replacement);
  });

  return applyDynamicBranding(context, value);
}
