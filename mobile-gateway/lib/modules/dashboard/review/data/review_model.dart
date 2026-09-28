class ReviewModel {
  final int? id;
  final int? barberShopId;
  final int? barberId;
  final int? customerId;
  final int? bookingId;
  final int? rating;
  final String? comment;

  const ReviewModel({
    this.id,
    this.barberShopId,
    this.barberId,
    this.customerId,
    this.bookingId,
    this.rating,
    this.comment,
  });

  factory ReviewModel.fromJson(Map<String, dynamic> json) => ReviewModel(
      id: json['id'] == null ? null : int.tryParse(json['id'].toString()),
      barberShopId: json['barber_shop_id'] == null ? null : int.tryParse(json['barber_shop_id'].toString()),
      barberId: json['barber_id'] == null ? null : int.tryParse(json['barber_id'].toString()),
      customerId: json['customer_id'] == null ? null : int.tryParse(json['customer_id'].toString()),
      bookingId: json['booking_id'] == null ? null : int.tryParse(json['booking_id'].toString()),
      rating: json['rating'] == null ? null : int.tryParse(json['rating'].toString()),
      comment: json['comment']?.toString(),
  );

  Map<String, dynamic> toJson() => {
      'id': id,
      'barber_shop_id': barberShopId,
      'barber_id': barberId,
      'customer_id': customerId,
      'booking_id': bookingId,
      'rating': rating,
      'comment': comment,
  };
}