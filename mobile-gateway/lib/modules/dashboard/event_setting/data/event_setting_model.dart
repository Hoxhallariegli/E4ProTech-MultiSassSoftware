class EventSettingModel {
  final int? id;
  final int? barberShopId;
  final int? realtimeEventId;
  final bool? reverbEnabled;
  final bool? firebaseEnabled;

  const EventSettingModel({
    this.id,
    this.barberShopId,
    this.realtimeEventId,
    this.reverbEnabled,
    this.firebaseEnabled,
  });

  factory EventSettingModel.fromJson(Map<String, dynamic> json) => EventSettingModel(
      id: json['id'] == null ? null : int.tryParse(json['id'].toString()),
      barberShopId: json['barber_shop_id'] == null ? null : int.tryParse(json['barber_shop_id'].toString()),
      realtimeEventId: json['realtime_event_id'] == null ? null : int.tryParse(json['realtime_event_id'].toString()),
      reverbEnabled: json['reverb_enabled'] == true || json['reverb_enabled'] == 1 || json['reverb_enabled'] == '1',
      firebaseEnabled: json['firebase_enabled'] == true || json['firebase_enabled'] == 1 || json['firebase_enabled'] == '1',
  );

  Map<String, dynamic> toJson() => {
      'id': id,
      'barber_shop_id': barberShopId,
      'realtime_event_id': realtimeEventId,
      'reverb_enabled': reverbEnabled,
      'firebase_enabled': firebaseEnabled,
  };
}