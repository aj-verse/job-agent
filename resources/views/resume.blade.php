@extends('layouts.app')

@section('content')
<div class="mb-5">
    <h1 class="fw-extrabold mb-1" style="letter-spacing: -1px;">Resume Profile</h1>
    <p class="text-muted mb-0">Upload your resume to let AI build your target profile and keywords automatically.</p>
</div>

<div class="row g-4">
    <!-- Left column: Upload & Profile Panel -->
    <div class="col-12 col-lg-4">
        <!-- Modern File Upload Zone -->
        <div class="glass-card mb-4 text-center position-relative overflow-hidden">
            <h5 class="fw-bold mb-4 text-start"><i class="fa-solid fa-file-arrow-up text-primary me-2"></i> Upload Resume</h5>
            
            <form action="{{ route('resume.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="border border-dashed rounded-4 p-4 mb-4 position-relative" style="border-color: rgba(139, 92, 246, 0.3) !important; background: rgba(139, 92, 246, 0.02); transition: all 0.3s ease;" id="dropzone">
                    <i class="fa-solid fa-cloud-arrow-up text-gradient fs-1 mb-3"></i>
                    <p class="text-white fw-bold mb-1">Drag & Drop Resume here</p>
                    <p class="text-muted text-xs mb-3">Supports PDF and DOCX formats (Max 10MB)</p>
                    
                    <input type="file" name="resume" class="form-control text-xs opacity-0 position-absolute start-0 top-0 w-100 h-100 cursor-pointer" accept=".pdf,.docx" required id="fileInput" onchange="updateFileName(this)">
                    <div class="text-xs text-primary fw-bold" id="fileName">No file selected</div>
                </div>
                <button type="submit" class="btn btn-primary-grad w-100 py-2.5">
                    <i class="fa-solid fa-wand-magic-sparkles me-2"></i> Parse & Analyze
                </button>
            </form>
        </div>

        <!-- Profile Quick Facts Card -->
        @if($resume)
            <div class="glass-card">
                <div class="text-center py-2">
                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle" style="width: 76px; height: 76px; border: 1px solid rgba(139, 92, 246, 0.2);">
                        <i class="fa-solid fa-user-tie fs-2"></i>
                    </div>
                    <h4 class="fw-bold text-white mb-1">{{ $resume->name ?: 'Candidate Profile' }}</h4>
                    <span class="badge bg-indigo-subtle text-primary border border-primary border-opacity-20 px-3 py-2 rounded-pill text-xs mb-4" style="background-color: rgba(139, 92, 246, 0.12);">
                        {{ $resume->job_role ?: 'General Candidate' }}
                    </span>
                </div>

                <hr class="border-secondary border-opacity-20 my-3">

                <div class="d-flex flex-column gap-3 text-xs">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted"><i class="fa-solid fa-envelope me-1"></i> Email:</span>
                        <span class="fw-bold text-white text-break">{{ $resume->email ?: 'N/A' }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted"><i class="fa-solid fa-phone me-1"></i> Phone:</span>
                        <span class="fw-bold text-white">{{ $resume->phone ?: 'N/A' }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted"><i class="fa-solid fa-briefcase me-1"></i> Experience:</span>
                        <span class="fw-bold text-white">{{ $resume->experience_years }} Years</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted"><i class="fa-solid fa-location-dot me-1"></i> Target Loc:</span>
                        <span class="fw-bold text-white">{{ $resume->preferred_location ?: 'N/A' }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted"><i class="fa-solid fa-money-bill-wave me-1"></i> Exp Salary:</span>
                        <span class="fw-bold text-white">{{ $resume->expected_salary ?: 'N/A' }}</span>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Right column: Detailed tabs -->
    <div class="col-12 col-lg-8">
        @if($resume)
            <!-- Glassmorphic Tabs -->
            <ul class="nav nav-pills gap-2 mb-4" id="profileTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active px-4 py-2.5 rounded-3 fw-bold border border-secondary border-opacity-10 text-white" id="view-tab" data-bs-toggle="tab" data-bs-target="#view-pane" type="button" role="tab" aria-controls="view-pane" aria-selected="true" style="background: rgba(255, 255, 255, 0.02);">
                        <i class="fa-solid fa-eye me-2 text-primary"></i> Profile Data
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link px-4 py-2.5 rounded-3 fw-bold border border-secondary border-opacity-10 text-white" id="edit-tab" data-bs-toggle="tab" data-bs-target="#edit-pane" type="button" role="tab" aria-controls="edit-pane" aria-selected="false" style="background: rgba(255, 255, 255, 0.02);">
                        <i class="fa-solid fa-user-pen me-2 text-info"></i> Edit Details
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="profileTabsContent">
                <!-- VIEW Tab Pane -->
                <div class="tab-pane fade show active" id="view-pane" role="tabpanel" aria-labelledby="view-tab" tabindex="0">
                    <!-- Skills -->
                    <div class="glass-card mb-4">
                        <h5 class="fw-bold mb-4 text-white"><i class="fa-solid fa-tags text-primary me-2"></i> Parsed Skill Tags</h5>
                        <div class="d-flex flex-wrap gap-2">
                            @if(is_array($resume->skills))
                                @foreach($resume->skills as $skill)
                                    <span class="badge bg-indigo-subtle text-primary border border-primary border-opacity-20 px-3 py-2.5 rounded-pill fs-7 fw-semibold" style="background-color: rgba(139, 92, 246, 0.1);">
                                        {{ $skill }}
                                    </span>
                                @endforeach
                            @else
                                <span class="text-muted">No skills extracted.</span>
                            @endif
                        </div>
                    </div>

                    <!-- Work History -->
                    <div class="glass-card mb-4">
                        <h5 class="fw-bold mb-4 text-white"><i class="fa-solid fa-briefcase text-primary me-2"></i> Professional Experience</h5>
                        @if(is_array($resume->experience_details) && count($resume->experience_details) > 0)
                            <div class="d-flex flex-column gap-4">
                                @foreach($resume->experience_details as $job)
                                    <div class="p-3.5 rounded-4 border border-secondary border-opacity-10 position-relative" style="background: rgba(255, 255, 255, 0.01);">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="fw-bold text-white mb-1">{{ $job['role'] ?? 'N/A' }}</h6>
                                                <span class="text-xs text-muted fw-semibold">{{ $job['company'] ?? 'N/A' }}</span>
                                            </div>
                                            <span class="badge bg-dark border border-secondary border-opacity-20 text-muted px-2 py-1.5 rounded-3 text-xxs">
                                                {{ $job['duration'] ?? 'N/A' }}
                                            </span>
                                        </div>
                                        <p class="text-muted text-xs mb-0 mt-2">{{ $job['description'] ?? '' }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted mb-0">No experience parsed.</p>
                        @endif
                    </div>

                    <!-- Education -->
                    <div class="glass-card mb-4">
                        <h5 class="fw-bold mb-4 text-white"><i class="fa-solid fa-graduation-cap text-primary me-2"></i> Academic Education</h5>
                        @if(is_array($resume->education) && count($resume->education) > 0)
                            <div class="table-responsive">
                                <table class="table table-dark table-hover table-borderless align-middle mb-0 text-xs">
                                    <thead>
                                        <tr class="border-bottom border-secondary border-opacity-20 text-muted">
                                            <th class="py-3">Degree</th>
                                            <th class="py-3">Institution</th>
                                            <th class="py-3">Passing Year</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($resume->education as $edu)
                                            <tr>
                                                <td class="fw-bold text-white py-3">{{ $edu['degree'] ?? 'N/A' }}</td>
                                                <td class="text-muted py-3">{{ $edu['school'] ?? 'N/A' }}</td>
                                                <td class="text-muted py-3">{{ $edu['year'] ?? 'N/A' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted mb-0">No education details parsed.</p>
                        @endif
                    </div>

                    <!-- Certifications -->
                    <div class="glass-card">
                        <h5 class="fw-bold mb-4 text-white"><i class="fa-solid fa-award text-primary me-2"></i> Professional Certifications</h5>
                        <div class="d-flex flex-wrap gap-2">
                            @if(is_array($resume->certifications))
                                @foreach($resume->certifications as $cert)
                                    <span class="badge border border-secondary border-opacity-25 px-3 py-2.5 rounded-3 text-xs fw-semibold" style="background-color: rgba(255, 255, 255, 0.02); color: #e2e8f0;">
                                        <i class="fa-solid fa-certificate text-warning me-2"></i>{{ $cert }}
                                    </span>
                                @endforeach
                            @else
                                <p class="text-muted mb-0">No certifications parsed.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- EDIT Tab Pane -->
                <div class="tab-pane fade" id="edit-pane" role="tabpanel" aria-labelledby="edit-tab" tabindex="0">
                    <div class="glass-card">
                        <h5 class="fw-bold mb-4 text-white"><i class="fa-solid fa-user-pen text-primary me-2"></i> Update Profile Details</h5>
                        <form action="{{ route('resume.update') }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted text-xs">Full Name</label>
                                    <input type="text" name="name" class="form-control" value="{{ $resume->name }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted text-xs">Email Address</label>
                                    <input type="email" name="email" class="form-control" value="{{ $resume->email }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted text-xs">Phone Number</label>
                                    <input type="text" name="phone" class="form-control" value="{{ $resume->phone }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted text-xs">Experience (Years)</label>
                                    <input type="number" name="experience_years" step="0.1" class="form-control" value="{{ $resume->experience_years }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted text-xs">Target Job Role</label>
                                    <input type="text" name="job_role" class="form-control" value="{{ $resume->job_role }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted text-xs">Preferred Location</label>
                                    <input type="text" name="preferred_location" class="form-control" value="{{ $resume->preferred_location }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted text-xs">Current Salary</label>
                                    <input type="text" name="current_salary" class="form-control" value="{{ $resume->current_salary }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted text-xs">Expected Salary</label>
                                    <input type="text" name="expected_salary" class="form-control" value="{{ $resume->expected_salary }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label text-muted text-xs">Skills (Comma-separated)</label>
                                    <textarea name="skills" rows="3" class="form-control" required>{{ implode(', ', $resume->skills ?: []) }}</textarea>
                                </div>
                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-primary-grad">
                                        <i class="fa-solid fa-circle-check me-2"></i> Save Profile Details
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @else
            <div class="glass-card text-center py-5">
                <i class="fa-solid fa-file-lines fs-1 text-muted mb-4 opacity-50"></i>
                <h4 class="fw-bold mb-2">No Profile Uploaded Yet</h4>
                <p class="text-muted mx-auto mb-4" style="max-width: 450px;">Upload your resume in the sidebar panel to analyze your target keywords, job preferences, and learning paths.</p>
            </div>
        @endif
    </div>
</div>

<script>
    function updateFileName(input) {
        const file = input.files[0];
        const label = document.getElementById('fileName');
        if (file) {
            label.textContent = file.name;
            label.classList.add('text-success');
        } else {
            label.textContent = 'No file selected';
            label.classList.remove('text-success');
        }
    }
</script>
@endsection
