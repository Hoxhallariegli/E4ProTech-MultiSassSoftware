import 'package:flutter/widgets.dart';
import 'core_en.dart';
import 'core_sq.dart';

String coreTr(BuildContext context, String key, [Map<String, String> args = const {}]) {
  final locale = Localizations.localeOf(context).languageCode.toLowerCase();
  final source = locale == 'sq' ? coreSq : coreEn;

  var value = source[key] ?? coreEn[key] ?? key;
  args.forEach((name, replacement) {
    value = value.replaceAll('{$name}', replacement);
  });

  return value;
}
