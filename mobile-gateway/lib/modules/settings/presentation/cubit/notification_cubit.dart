import 'package:flutter_bloc/flutter_bloc.dart';
import '../../data/notification_repository.dart';
import 'notification_state.dart';

class NotificationCubit extends Cubit<NotificationState> {
  final NotificationRepository repository;

  NotificationCubit(this.repository) : super(const NotificationInitial());

  Future<void> loadSettings() async {
    emit(const NotificationLoading());
    try {
      final data = await repository.getSettings();
      emit(NotificationLoaded(
        modules: data['modules'] ?? [],
        events: data['events'] ?? [],
      ));
    } catch (e) {
      emit(NotificationError(e.toString()));
    }
  }

  Future<void> toggleModule(String moduleName) async {
    final currentState = state;
    if (currentState is! NotificationLoaded) return;

    try {
      final isEnabled = await repository.toggleModule(moduleName);

      final updatedModules = currentState.modules.map((m) {
        if (m['name'] == moduleName) {
          return {...m, 'enabled': isEnabled};
        }
        return m;
      }).toList();

      // Kur ndryshojmë modulin, duhet të përditësojmë edhe eventet e lidhura te UI
      // Kjo do të bëhet automatikisht në reload, por mund ta bëjmë edhe lokalisht.
      loadSettings(); // Reload for full sync
    } catch (e) {
      emit(NotificationError(e.toString()));
    }
  }

  Future<void> toggleEvent(int eventId) async {
    final currentState = state;
    if (currentState is! NotificationLoaded) return;

    try {
      final isEnabled = await repository.toggleEvent(eventId);

      final updatedEvents = currentState.events.map((e) {
        if (e['id'] == eventId) {
          return {...e, 'firebase_enabled': isEnabled};
        }
        return e;
      }).toList();

      emit(NotificationLoaded(
        modules: currentState.modules,
        events: updatedEvents,
      ));
    } catch (e) {
      emit(NotificationError(e.toString()));
    }
  }
}
