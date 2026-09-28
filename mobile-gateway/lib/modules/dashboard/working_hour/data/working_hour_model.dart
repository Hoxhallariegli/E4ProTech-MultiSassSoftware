class WorkingHourModel {
  final int? id;
  final int? barberId;
  final String? dayOfWeek;
  final String? openTime;
  final String? closeTime;
  final String? lunchStart;
  final String? lunchEnd;
  final bool? isClosed;

  const WorkingHourModel({
    this.id,
    this.barberId,
    this.dayOfWeek,
    this.openTime,
    this.closeTime,
    this.lunchStart,
    this.lunchEnd,
    this.isClosed,
  });

  factory WorkingHourModel.fromJson(Map<String, dynamic> json) => WorkingHourModel(
      id: json['id'] == null ? null : int.tryParse(json['id'].toString()),
      barberId: json['barber_id'] == null ? null : int.tryParse(json['barber_id'].toString()),
      dayOfWeek: json['day_of_week']?.toString(),
      openTime: json['open_time']?.toString(),
      closeTime: json['close_time']?.toString(),
      lunchStart: json['lunch_start']?.toString(),
      lunchEnd: json['lunch_end']?.toString(),
      isClosed: json['is_closed'] == true || json['is_closed'] == 1 || json['is_closed'] == '1',
  );

  Map<String, dynamic> toJson() => {
      'id': id,
      'barber_id': barberId,
      'day_of_week': dayOfWeek,
      'open_time': openTime,
      'close_time': closeTime,
      'lunch_start': lunchStart,
      'lunch_end': lunchEnd,
      'is_closed': isClosed,
  };
}
