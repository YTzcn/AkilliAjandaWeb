<section class="mb-4">
    <div class="card border-danger">
        <div class="card-header bg-danger bg-opacity-10 text-danger">
            <h2 class="card-title">{{ __('Hesabı Sil') }}</h2>
            <p class="text-muted small">{{ __('Hesabınız silindiğinde, tüm verileriniz ve kaynaklar kalıcı olarak silinecektir. Hesabınızı silmeden önce saklamak istediğiniz verileri veya bilgileri indirin.') }}</p>
        </div>
        <div class="card-body">
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmUserDeletionModal">
                <i class="bi bi-trash-fill me-2"></i>{{ __('Hesabı Sil') }}
            </button>
            
            <!-- Hesap Silme Onay Modalı -->
            <div class="modal fade" id="confirmUserDeletionModal" tabindex="-1" aria-labelledby="confirmUserDeletionModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="post" action="{{ route('profile.destroy') }}">
                            @csrf
                            @method('delete')
                            
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title" id="confirmUserDeletionModalLabel">{{ __('Hesabınızı silmek istediğinizden emin misiniz?') }}</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            
                            <div class="modal-body">
                                <p>{{ __('Hesabınız silindiğinde, tüm verileriniz ve kaynaklar kalıcı olarak silinecektir. Bu işlem geri alınamaz.') }}</p>
                                
                                <div class="mb-3">
                                    <label for="password" class="form-label">{{ __('Şifre') }}</label>
                                    <input type="password" class="form-control @error('password', 'userDeletion') is-invalid @enderror" id="password" name="password" placeholder="{{ __('Devam etmek için şifrenizi girin') }}">
                                    @error('password', 'userDeletion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('İptal') }}</button>
                                <button type="submit" class="btn btn-danger">{{ __('Hesabı Sil') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section> 