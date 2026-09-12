import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:mobile_gateway/modules/settings/data/language_repository.dart';

class LocaleState {
  final Locale locale;
  final List<Locale> supportedLocales;

  LocaleState({required this.locale, required this.supportedLocales});
}

class LocaleCubit extends Cubit<LocaleState> {
  LocaleCubit() : super(LocaleState(
    locale: const Locale('en'),
    supportedLocales: const [Locale('en'), Locale('sq')],
  )) {
    _init();
  }

  Future<void> _init() async {
    await _loadSavedLocale();
    await refreshSupportedLocales();
  }

  Future<void> _loadSavedLocale() async {
    final prefs = await SharedPreferences.getInstance();
    final langCode = prefs.getString('app_locale') ?? 'en';
    emit(LocaleState(
      locale: Locale(langCode),
      supportedLocales: state.supportedLocales,
    ));
  }

  Future<void> refreshSupportedLocales() async {
    try {
      final repo = LanguageRepository();
      final langs = await repo.getLanguages();
      final locales = langs.map((l) => Locale(l['code'] ?? 'en')).toList();

      if (locales.isNotEmpty) {
        emit(LocaleState(
          locale: state.locale,
          supportedLocales: locales,
        ));
      }
    } catch (_) {}
  }

  Future<void> setLocale(String langCode) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('app_locale', langCode);
    emit(LocaleState(
      locale: Locale(langCode),
      supportedLocales: state.supportedLocales,
    ));
  }
}
