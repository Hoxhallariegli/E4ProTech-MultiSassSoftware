import 'package:flutter/widgets.dart';
import 'shop_front_page_setting_en.dart';
import 'shop_front_page_setting_sq.dart';

String shop_front_page_settingTr(
  BuildContext context,
  String key, [
  Map<String, String> args = const {},
]) {
  final language = Localizations.localeOf(context).languageCode.toLowerCase();

  Map<String, String> getSource() {
    if (language == 'en') return shop_front_page_settingEn;
    if (language == 'sq') return shop_front_page_settingSq;
    return shop_front_page_settingEn;
  }

  final source = getSource();
  var value = source[key] ?? shop_front_page_settingEn[key] ?? key;
  args.forEach((name, replacement) {
    value = value.replaceAll('{$name}', replacement);
  });

  return value;
}