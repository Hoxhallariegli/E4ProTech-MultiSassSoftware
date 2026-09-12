import 'package:flutter/widgets.dart';
import 'pagination_en.dart';
import 'pagination_sq.dart';

String paginationTr(
  BuildContext context,
  String key, [
  Map<String, String> args = const {},
]) {
  final language = Localizations.localeOf(context).languageCode.toLowerCase();
  final source = language == 'sq' ? paginationSq : paginationEn;

  var value = source[key] ?? paginationEn[key] ?? key;
  args.forEach((name, replacement) {
    value = value.replaceAll('{${name}}', replacement);
  });

  return value;
}